<?php
declare(strict_types=1);

function interiorPhoto(string $key): array
{
    static $photos = null;
    if ($photos === null) {
        $photos = [];
        $credits = json_decode(
            file_get_contents(BASE_PATH . '/public/assets/images/credits.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        foreach ($credits as $entry) {
            if ($entry['width'] === 1600) {
                $photos[$entry['key']] = $entry;
            }
        }
    }
    if (!isset($photos[$key])) {
        throw new InvalidArgumentException('Ảnh không có trong danh mục.');
    }
    return $photos[$key];
}

function photoAttributes(string $key, bool $eager = false): string
{
    $photo = interiorPhoto($key);
    $base = 'assets/images/' . $key;
    return 'src="' .
        e($base . '.jpg') .
        '" srcset="' .
        e($base . '-small.jpg') .
        ' 640w, ' .
        e($base . '.jpg') .
        ' 1600w" sizes="(max-width: 760px) 100vw, (max-width: 1200px) 60vw, 800px"' .
        ' alt="' .
        e($photo['alt'] . ' — ảnh tham khảo') .
        '" decoding="async"' .
        ($eager ? ' loading="eager" fetchpriority="high"' : ' loading="lazy"');
}

function servicePhotoKey(string $type): string
{
    return match ($type) {
        'DESIGN' => 'cozy-corner',
        'INSTALLATION' => 'woodworking',
        'MAINTENANCE' => 'workshop-tools',
        default => 'living-room'
    };
}

function ideaPhotoKey(string $slug): string
{
    return match ($slug) {
        'modern' => 'kitchen-dining',
        'studio' => 'cozy-corner',
        'warm' => 'warm-bedroom',
        default => 'living-room'
    };
}

function apartmentPhotoKey(int $id): string
{
    return ['living-room', 'kitchen-dining', 'warm-bedroom', 'cozy-corner'][max(0, $id - 1) % 4];
}
