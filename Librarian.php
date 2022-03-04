<?php

namespace NJUPTAAA\Babel\Biblioteca;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Librarian
{
    private static function log($format, ...$values)
    {
        printf("\033[94m%s ╎\033[0m $format\n", date('Y-m-d H:i:s'), ...$values);
    }

    private static function directory(...$values)
    {
        return implode(DIRECTORY_SEPARATOR, $values);
    }

    private static function publish($path, $content)
    {
        if (!is_dir(pathinfo($path, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($path, PATHINFO_DIRNAME), 0755, true);
        }
        file_put_contents($path, $content);
    }

    private static $onlineJudges = ['CodeForces'];
    private $debug = true;
    private $publicDir = 'public';

    private static function cleanUp($dir)
    {
        if (is_dir($dir)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $fileinfo) {
                ($fileinfo->isDir() ? 'rmdir' : 'unlink')($fileinfo->getRealPath());
            }
            rmdir($dir);
        }
    }

    private function extractDialect($dialectDir)
    {
        $dialect = ['title' => null, 'authors' => []];
        $configPath = Librarian::directory($dialectDir, 'biblioteca.json');
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath));
            $dialect['title'] = $config->title ?? null;
        }
        foreach (['description', 'input', 'output', 'note'] as $markdownSource) {
            $markdownPath = Librarian::directory($dialectDir, "$markdownSource.md");
            if (file_exists($markdownPath)) {
                $dialect[$markdownSource] = preg_replace('~(*BSR_ANYCRLF)\R~', "\n", file_get_contents($markdownPath));
            }
        }
        return $dialect;
    }

    public function __construct($debugMode = true)
    {
        Librarian::log("Biblioteca Librarian starting...");
        $this->debug = $debugMode;
        Librarian::cleanUp(Librarian::directory(__DIR__, $this->publicDir));
        Librarian::log("Biblioteca Librarian cleaned up.");
    }

    public function classifier()
    {
        Librarian::log("Biblioteca Librarian processing...");
        foreach (Librarian::$onlineJudges as $onlineJudge) {
            Librarian::log("Processing Online Judge: \033[92m$onlineJudge\033[0m");
            [$onlineJudgeDir, $problemCatalogs] = [Librarian::directory(__DIR__, $onlineJudge), []];
            foreach (new DirectoryIterator($onlineJudgeDir) as $problemInfo) {
                if (!$problemInfo->isDot() && $problemInfo->isDir()) {
                    $pcode = $problemInfo->getFilename();
                    Librarian::log("- Packing Problem: \033[93m%s\033[0m", $pcode);
                    [$problemDir, $problemDialects] = [Librarian::directory($onlineJudgeDir, $pcode), []];
                    foreach (new DirectoryIterator($problemDir) as $dialectInfo) {
                        if (!$dialectInfo->isDot() && $dialectInfo->isDir()) {
                            $dialect = $dialectInfo->getFilename();
                            $problemDialects[$dialect] = $this->extractDialect(Librarian::directory($problemDir, $dialect));
                            $problemCatalogs[$pcode][] = $dialect;
                        }
                    }
                    Librarian::publish(Librarian::directory(__DIR__, $this->publicDir, $onlineJudge, "$pcode.min.json"), json_encode($problemDialects, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    if ($this->debug) Librarian::publish(Librarian::directory(__DIR__, $this->publicDir, $onlineJudge, "$pcode.json"), json_encode($problemDialects, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                }
            }
            Librarian::publish(Librarian::directory(__DIR__, $this->publicDir, $onlineJudge, "catalog.min.json"), json_encode($problemCatalogs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            if ($this->debug) Librarian::publish(Librarian::directory(__DIR__, $this->publicDir, $onlineJudge, "catalog.json"), json_encode($problemCatalogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        Librarian::publish(Librarian::directory(__DIR__, $this->publicDir, "index.md"), file_get_contents(Librarian::directory(__DIR__, "README.md")));
        Librarian::log("Biblioteca Librarian processed.");
    }
}

if ($argc >= 2 && $argv[1] == 'production') {
    $debugMode = false;
}

(new Librarian($debugMode ?? true))->classifier();
