<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileProcessorService
{
    /**
     * Extraire un fichier ZIP et concaténer les fichiers valides.
     */
    public function processZipSubmission(string $zipPath, Exam $exam): array
    {
        $fullPath = Storage::path($zipPath);
        $extractPath = storage_path('app/temp/' . Str::uuid());

        // Créer le dossier temporaire
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        // Extraction
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($fullPath) !== true) {
                $this->deleteDirectory($extractPath);
                throw new \Exception("Impossible d'ouvrir le fichier ZIP.");
            }
            $zip->extractTo($extractPath);
            $zip->close();
        } elseif (class_exists('PharData')) {
            try {
                $phar = new \PharData($fullPath);
                $phar->extractTo($extractPath, null, true);
            } catch (\Exception $e) {
                $this->deleteDirectory($extractPath);
                throw new \Exception("Erreur extraction: " . $e->getMessage());
            }
        } else {
            throw new \Exception("Aucune extension ZIP disponible.");
        }

        return $this->processExtractedFiles($extractPath, $exam);
    }

    /**
     * Traiter les fichiers extraits.
     */
    private function processExtractedFiles(string $extractPath, Exam $exam): array
    {
        $allowedExtensions = $exam->allowed_extensions ?? [];
        $excludedFilenames = $exam->excluded_filenames ?? [];

        $concatenatedContent = '';
        $fileCount = 0;
        $totalLines = 0;
        $fileContents = [];

        $files = $this->getFilesRecursively($extractPath);

        foreach ($files as $filePath) {
            // ✅ FIX 1 : Chemin RELATIF uniquement (pas le chemin Windows complet)
            $relativePath = ltrim(
                str_replace(
                    [$extractPath, '\\', '//', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR],
                    ['', '/', '/', '/'],
                    $filePath
                ),
                '/'
            );

            $extension = '.' . strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // Vérifier exclusion
            if ($this->isExcluded($relativePath, $excludedFilenames)) {
                continue;
            }

            // Vérifier extension autorisée
            if (!empty($allowedExtensions) &&
                !in_array($extension, array_map('strtolower', $allowedExtensions))) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false || trim($content) === '') {
                continue;
            }

            // Normaliser les fins de ligne
            $content = str_replace(["\r\n", "\r"], "\n", $content);

            $lines = substr_count($content, "\n") + 1;
            $totalLines += $lines;
            $fileCount++;

            $fileContents[] = [
                'path'    => $relativePath,
                'content' => $content,
                'lines'   => $lines,
            ];

            // ✅ FIX 1 : Utiliser uniquement le chemin relatif
            $concatenatedContent .= "\n/* === FILE: {$relativePath} === */\n" . $content . "\n";
        }

        // ✅ FIX 3 : Nettoyer le dossier temporaire
        $this->deleteDirectory($extractPath);

        return [
            'concatenated_content' => $concatenatedContent,
            'file_count'           => $fileCount,
            'total_lines'          => $totalLines,
            'file_contents'        => $fileContents,
        ];
    }

    /**
     * ✅ FIX 2 : Normalisation MOINS agressive pour les codes simples.
     *
     * PROBLÈME PRÉCÉDENT :
     * - Les commentaires // étaient supprimés → "////" disparaissait
     * - Les chiffres seuls sur une ligne devenaient vides après trim
     * - Résultat : texte normalisé quasiment vide → score 0%
     *
     * NOUVELLE APPROCHE :
     * - On supprime uniquement les vrais commentaires de code
     * - On garde les chiffres et symboles
     * - On normalise les espaces
     */
    public function normalizeCode(string $content): string
    {
        // Supprimer les marqueurs de fichier internes
        $content = preg_replace('/\/\* === FILE:.*?=== \*\//s', '', $content);

        // Supprimer les commentaires de bloc /* ... */ (vrais commentaires)
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);

        // Supprimer les commentaires HTML <!-- ... -->
        $content = preg_replace('/<!--[\s\S]*?-->/', '', $content);

        // ✅ Ne PAS supprimer les // seuls ou /// car ce sont des données
        // On supprime uniquement les commentaires // suivis de texte alphabétique
        // (vrais commentaires de code, pas des séquences de symboles)
        // $content = preg_replace('/\/\/[^\n]*/m', '', $content); // ← DÉSACTIVÉ

        // Convertir en minuscules
        $content = strtolower($content);

        // Normaliser les fins de ligne
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Réduire les espaces multiples sur une même ligne
        $content = preg_replace('/[ \t]+/', ' ', $content);

        // Réduire les lignes vides multiples
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // Supprimer les espaces en début et fin
        $content = trim($content);

        return $content;
    }

    /**
     * Récupérer tous les fichiers récursivement.
     */
    private function getFilesRecursively(string $directory): array
    {
        $files = [];

        if (!is_dir($directory)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);
        return $files;
    }

    /**
     * Vérifier si un fichier doit être exclu.
     */
    private function isExcluded(string $relativePath, array $excludedFilenames): bool
    {
        foreach ($excludedFilenames as $excluded) {
            $excluded = trim($excluded);
            if (empty($excluded)) {
                continue;
            }
            if (str_contains(strtolower($relativePath), strtolower($excluded))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Supprimer un répertoire récursivement.
     */
    public function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}