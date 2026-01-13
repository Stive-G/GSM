<?php

namespace App\Service\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProductImageStorage
{
    public function __construct(
        private readonly string $publicUploadDir, // ex: %kernel.project_dir%/public/uploads
        private readonly SluggerInterface $slugger,
    ) {}

    /**
     * @param UploadedFile[] $files
     * @return array{paths: string[], errors: string[]}
     */
    public function storeProductImages(string $sku, array $files): array
    {
        $files = is_array($files) ? array_values(array_filter($files)) : [];
        if ($files === []) return ['paths' => [], 'errors' => []];

        $skuSafe = (string) $this->slugger->slug(trim($sku))->lower();
        if ($skuSafe === '') $skuSafe = 'no-sku';

        $targetDir = rtrim($this->publicUploadDir, '/') . '/products/' . $skuSafe;
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Impossible de créer le dossier : ' . $targetDir);
            }
        }

        $paths = [];
        $errors = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                $errors[] = "Fichier invalide.";
                continue;
            }

            $original = (string) $file->getClientOriginalName();

            if (!$file->isValid()) {
                $errors[] = "Upload échoué pour {$original}.";
                continue;
            }

            // taille max 10Mo
            $size = $file->getSize();
            if ($size !== null && $size > 10 * 1024 * 1024) {
                $errors[] = "{$original} : fichier trop lourd (max 10 Mo).";
                continue;
            }

            // Vérifie contenu réel
            $tmp = $file->getPathname();
            $imgInfo = @getimagesize($tmp);
            if ($imgInfo === false) {
                $errors[] = "{$original} : ce n'est pas une image valide.";
                continue;
            }

            // Types autorisés
            $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
            if (!in_array($imgInfo[2] ?? null, $allowedTypes, true)) {
                $errors[] = "{$original} : format non supporté (jpg/png/webp).";
                continue;
            }

            $ext = match ($imgInfo[2]) {
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG  => 'png',
                IMAGETYPE_WEBP => 'webp',
                default        => 'png',
            };

            $name = bin2hex(random_bytes(16)) . '.' . $ext;

            try {
                $file->move($targetDir, $name);
            } catch (\Throwable $e) {
                $errors[] = "{$original} : erreur lors de l'enregistrement.";
                continue;
            }

            $paths[] = '/uploads/products/' . $skuSafe . '/' . $name;
        }

        return ['paths' => $paths, 'errors' => $errors];
    }
}
