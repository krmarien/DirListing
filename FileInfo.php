<?php

class FileInfo
{
    private $pathName;
    private $fileName;
    private $modifiedDate;
    private $size;
    private $isDir;
    private $extension;

    public function __construct(DirectoryIterator $fileInfo, $calculateSize)
    {
        $this->pathName = $fileInfo->getPathname();
        $this->fileName = $fileInfo->getFileName();
        $this->modifiedDate = new DateTime('@' . $fileInfo->getCTime());
        $this->isDir = $fileInfo->isDir();
        $this->size = $this->isDir ? null : $fileInfo->getSize();
        $this->extension = $fileInfo->getExtension();

        if ($calculateSize)
            $this->getSize();
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    public function getSize()
    {
        if (null === $this->size && $this->isDir) {
            $io = popen('/usr/bin/du -sh ' . escapeshellarg($this->pathName), 'r');
            $this->size = fgets($io, 4096);
            $multiplier = 1;
            switch (substr($this->size, strpos($this->size, "\t" )-1, 1)) {
                case 'K':
                    $multiplier = 1000;
                    break;
                case 'M':
                    $multiplier = 1000000;
                    break;
                case 'G':
                    $multiplier = 1000000000;
                    break;
                case 'T':
                    $multiplier = 1000000000000;
                    break;
                case 'P':
                    $multiplier = 1000000000000000;
                    break;
            }
            $this->size = substr($this->size, 0, strpos($this->size, "\t" )-1) * $multiplier;
            pclose($io);
        }

        return $this->size;
    }

    public function getSizeFormatted()
    {
        $size = $this->size;
        $sizes = array('&nbspB', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $count = 0;
        while ($count < (count($sizes)-1) && $size > 1024) {
            $size = $size/1024;
            $count++;
        }
        $result = sprintf("%.2f %s", $size, $sizes[$count]);

        return $result;
    }

    public function getIcon()
    {
        if ($this->isDir)
            return null;

        $types = array(
            'audio' => array('aif', 'iff', 'm3u', 'm4a', 'mid', 'mp3', 'mpa', 'ra', 'wav', 'wma'),
            'video' => array('avi', 'mkv', '3gp', 'asf', 'asx', '3g2', 'flv', 'm4v', 'mov', 'mp4', 'mpg', 'rm', 'srt', 'swf', 'vob', 'wmv'),
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'psd', 'pspimage', 'tga', 'thm', 'tif', 'tiff', 'yuv', 'svg', 'bmp', 'dds'),
            'text' => array('doc', 'docx', 'log', 'msg', 'odt', 'pages', 'rtf', 'tex', 'txt', 'wpd', 'wps', 'pdf'),
            'zip' => array('7z', 'deb', 'gz', 'pkg', 'rar', 'rpm', '.tar.gz', 'zip', 'zipx', 'jar'),
            'disk' => array('bin', 'cue', 'dmg', 'iso', 'mdf', 'toast', 'vcd'),
            'code' => array('java', 'c', 'class', 'pl', 'py', 'sh', 'cpp', 'cs', 'dtd', 'fla', 'h', 'lua', 'm', 'sln'),
            'excel' => array('xlr', 'xls', 'xlsx'),
        );

        $icons = array(
            'audio' => 'glyphicon-music',
            'video' => 'glyphicon-film',
            'image' => 'glyphicon-picture',
            'text' => 'glyphicon-file',
            'zip' => 'glyphicon-compressed',
            'disk' => 'glyphicon-record',
            'code' => 'glyphicon-indent-left',
            'excel' => 'glyphicon-list-alt',
            'generic' => 'glyphicon-unchecked',
        );

        foreach ($types as $type => $extensions) {
            if (in_array($this->extension, $extensions))
                return $icons[$type];
        }

        return $icons['generic'];
    }
}