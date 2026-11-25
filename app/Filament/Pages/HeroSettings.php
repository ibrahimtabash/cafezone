<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\SiteSetting;
use App\Support\CompressedImageUploader;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HeroSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'صور الهيرو';

    protected static ?string $title = 'صور الهيرو (الواجهة العلوية للصفحات)';

    protected static ?string $slug = 'hero-images';

    /** @var array<string, mixed> */
    public ?array $data = [];

    private const KEYS = [
        'home_hero_image', 'home_hero_mobile',
        'winter_hero_image', 'winter_hero_mobile', 'winter_hero_category',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageCatalog() === true;
    }

    public function mount(): void
    {
        $this->form->fill(collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => SiteSetting::get($k)])->all());
    }

    private static function upload(string $name, string $label, string $help): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->disk('public')
            ->directory('hero')
            ->maxSize(10240)
            ->saveUploadedFileUsing(fn ($file): string => CompressedImageUploader::store($file, 'hero'))
            ->helperText($help);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الصفحة الرئيسية')
                    ->description('إن لم ترفع صورة تُستخدم الصورة الثابتة الافتراضية.')
                    ->columns(2)
                    ->schema([
                        self::upload('home_hero_image', 'صورة الديسكتوب والتابلت', 'يفضّل صورة عريضة (مثلاً 1920×800).'),
                        self::upload('home_hero_mobile', 'صورة الجوال (اختيارية)', 'يفضّل صورة شبه مربعة أو طولية (مثلاً 900×1100). إن تُركت فارغة تُستخدم صورة الديسكتوب.'),
                    ]),
                Section::make('الصفحة الشتوية')
                    ->description('الأولوية: الصورة المرفوعة، ثم صورة القسم المختار، ثم الصورة الثابتة.')
                    ->columns(2)
                    ->schema([
                        self::upload('winter_hero_image', 'صورة الديسكتوب والتابلت', 'يفضّل صورة عريضة (مثلاً 1920×800).'),
                        self::upload('winter_hero_mobile', 'صورة الجوال (اختيارية)', 'إن تُركت فارغة تُستخدم صورة الديسكتوب.'),
                        Select::make('winter_hero_category')
                            ->label('أو استخدم صورة قسم')
                            ->options(fn () => Category::query()->whereNotNull('image')->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('بدون')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('حفظ')->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (self::KEYS as $key) {
            $value = $data[$key] ?? null;
            $value = is_array($value) ? (array_values($value)[0] ?? null) : $value;

            SiteSetting::put($key, filled($value) ? (string) $value : null);
        }

        Notification::make()->title('تم الحفظ')->success()->send();
    }
}
