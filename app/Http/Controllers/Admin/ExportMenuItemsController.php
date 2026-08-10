<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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

class ExportMenuItemsController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $items = MenuItem::query()
            ->with('category:id,name')
            ->latest()
            ->get();

        $exportDirectory = storage_path('app/private/exports');
        File::ensureDirectoryExists($exportDirectory);
        $fileName = 'menu-products-' . now()->format('Y-m-d-His') . '.xlsx';
        $filePath = $exportDirectory . DIRECTORY_SEPARATOR . Str::uuid() . '.xlsx';

        $options = new Options();
        $options->setColumnWidth(30, 1);
        $options->setColumnWidth(16, 2);
        $options->setColumnWidth(70, 3);
        $options->setColumnWidth(28, 4);

        $writer = new Writer($options);
        $writer->setCreator('Zone Cafe');
        $writer->openToFile($filePath);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('المنتجات');
        $sheet->setSheetView(
            (new SheetView())
                ->setRightToLeft(true)
                ->setFreezeRow(2)
                ->setZoomScale(95)
        );
        $sheet->setAutoFilter(new AutoFilter(0, 1, 3, max(1, $items->count() + 1)));

        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setFontSize(12)
            ->setBackgroundColor('7B3526')
            ->setCellAlignment(CellAlignment::CENTER);

        $priceStyle = (new Style())
            ->setFormat('#,##0.00 "₪"')
            ->setCellAlignment(CellAlignment::CENTER);

        $urlStyle = (new Style())
            ->setFontColor(Color::BLUE)
            ->setFontUnderline()
            ->setShouldWrapText();

        $writer->addRow(
            Row::fromValues(['اسم المنتج', 'السعر', 'رابط الصورة', 'التصنيف'], $headerStyle)
                ->setHeight(26)
        );

        foreach ($items as $item) {
            $imageUrl = $item->image ? Storage::disk('public')->url($item->image) : '';
            if ($imageUrl !== '' && ! Str::startsWith($imageUrl, ['http://', 'https://'])) {
                $imageUrl = url($imageUrl);
            }

            $writer->addRow(Row::fromValuesWithStyles([
                $item->name,
                (float) $item->price,
                $imageUrl,
                $item->category?->name ?? 'بدون تصنيف',
            ], columnStyles: [
                1 => $priceStyle,
                2 => $urlStyle,
            ]));
        }

        $writer->close();

        return response()
            ->download($filePath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }
}
