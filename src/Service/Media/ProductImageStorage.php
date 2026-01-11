<?php

namespace App\Service\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProductImageStorage
{
    public function __construct(
        private readonly string $publicUploadDir,
        private readonly SluggerInterface $slugger,
    ) {}

    /**
     * @param UploadedFile[] $files
     * @return string[] chemins publics (/uploads/...)
     */
    public function storeProductImages(string $sku, array $files): array
    {
        $files = array_filter($files);
        $files = is_array($files) ? $files : [];

        $sku = trim($sku);
        $skuSafe = (string) $this->slugger->slug($sku)->lower();
        if ($skuSafe === '') {
            $skuSafe = 'no-sku';
        }

        $targetDir = rtrim($this->publicUploadDir, '/') . '/products/' . $skuSafe;

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $paths = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            if ($file->getError() !== UPLOAD_ERR_OK) continue;

            // limite taille (4 Mo)
            $size = $file->getSize();
            if ($size !== null && $size > 4 * 1024 * 1024) {
                continue;
            }

            // whitelist mime
            $mime = (string) $file->getMimeType();
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                continue;
            }

            $ext = $file->guessExtension() ?: 'bin';
            $name = bin2hex(hash('sha256', uniqid('', true), true)) . '.' . $ext;
            $file->move($targetDir, $name);

            $paths[] = '/uploads/products/' . $skuSafe . '/' . $name;
        }

        return $paths;
    }
}
