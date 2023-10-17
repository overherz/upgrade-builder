<?php


namespace upgrade\builder;

/**
 * Class ReleaseArchive
 * @package upgrade\builder
 */
class ReleaseArchive implements ReleaseArchiveInterface
{
    /**
     * Archive file
     * @var string
     */
    protected $file;

    /**
     * Edition
     * @var string|null
     */
    protected $name = null;

    /**
     * Version
     * @var string|null
     */
    protected $version = null;

    /**
     * Archiver
     * @var ArchiverInterface|null
     */
    protected $archiver = null;

    /**
     * ReleaseArchive constructor.
     * @param string $file
     * @param string $name
     * @param string $version
     * @throws \Exception
     */
    public function __construct($file, $name, $version)
    {
        if (!is_file($file)) {
            throw new \Exception('File not found.');
        }

        $this->name = $name;
        $this->version = $version;
        $this->file = $file;
        $this->archiver = $this->getArchiver();
    }
    /**
     * Get product name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get archive path
     * @return string
     */
    public function getPath()
    {
        return $this->file;
    }

    /**
     * Get version
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Extract archive to directory
     * @param string $path
     * @return bool
     */
    public function extractTo($path)
    {
        $result = $this->archiver->extract($this->file, $path);
        $this->deleteUnnecessaryFiles($path);

        return $result;
    }

    private function deleteUnnecessaryFiles($dir) {
        if (!is_dir($dir)) {
            return null;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $this->deleteUnnecessaryFiles($filePath);
            } elseif (is_file($filePath)) {
                if (preg_match('~^\._~ui', basename($filePath))) {
                    unlink($filePath);
                }
            }
        }
    }

    public function getArchiver()
    {
        $name_parts = explode('.', strtolower($this->file));
        $ext = end($name_parts);
        if ($ext == 'zip') {
            return new ZipArchiver();
        }

        return new TarArchiver();
    }
}