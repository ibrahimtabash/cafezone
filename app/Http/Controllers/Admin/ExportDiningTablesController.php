<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportDiningTablesController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $tables = DiningTable::query()->withCount('orders')->orderBy('name')->get();
        $exportDirectory = storage_path('app/private/exports');
        File::ensureDirectoryExists($exportDirectory);
        $fileName = 'dining-tables-qrcodes-' . now()->format('Y-m-d-His') . '.xlsx';
        $filePath = $exportDirectory . DIRECTORY_SEPARATOR . Str::uuid() . '.xlsx';

        $options = new Options();
        $options->setColumnWidth(24, 1);
        $options->setColumnWidth(20, 2);
        $options->setColumnWidth(14, 3, 4, 5);
        $options->setColumnWidth(62, 6, 7);

        $writer = new Writer($options);
        $writer->setCreator('Zone Cafe');
        $writer->openToFile($filePath);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('الطاولات وQR');
        $sheet->setSheetView((new SheetView())->setRightToLeft(true)->setFreezeRow(2)->setZoomScale(90));
        $sheet->setAutoFilter(new AutoFilter(0, 1, 6, max(1, $tables->count() + 1)));

        $headerStyle = (new Style())->setFontBold()->setFontColor(Color::WHITE)
            ->setFontSize(12)->setBackgroundColor('7B3526')->setCellAlignment(CellAlignment::CENTER);
        $centerStyle = (new Style())->setCellAlignment(CellAlignment::CENTER);
        $linkStyle = (new Style())->setFontColor(Color::BLUE)->setFontUnderline()->setShouldWrapText();

        $writer->addRow(Row::fromValues([
            'رقم / اسم الطاولة', 'رمز الطاولة', 'عدد المقاعد', 'الحالة', 'عدد الطلبات',
            'رابط صورة QR', 'رابط الطلب',
        ], $headerStyle)->setHeight(28));

        foreach ($tables as $table) {
            $orderUrl = route('menu.table', $table);
            $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . rawurlencode($orderUrl);

            $writer->addRow(Row::fromValuesWithStyles([
                $table->name,
                $table->code,
                (int) $table->capacity,
                $table->is_active ? 'فعالة' : 'غير فعالة',
                (int) $table->orders_count,
                $qrImageUrl,
                $orderUrl,
            ], columnStyles: [
                2 => $centerStyle, 3 => $centerStyle, 4 => $centerStyle,
                5 => $linkStyle, 6 => $linkStyle,
            ])->setHeight(34));
        }

        $writer->close();

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
