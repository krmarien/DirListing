<?php

require('FileInfo.php');

class DirListing
{
    private $showHiddenFiles = false;
    private $showFolderSize = false;
    private $showLastModified = false;

    private $root;
    private $current;

    private $folderList;
    private $fileList;

    public function __construct($root)
    {
        $this->root = rtrim($root, '/');
        $this->_current = trim(urldecode($_SERVER['REQUEST_URI']), '/') == '' ?  '/' : '/' . trim(urldecode($_SERVER['REQUEST_URI']), '/') . '/';
    }

    public function getBreadCrumb()
    {
        $folders = explode('/', trim($this->getCurrentPath(), '/'));
        $fathers = count($folders);

        $breadCrumb = array(
            array(
                'link' => '/',
                'label' => '..',
                'active' => $this->getCurrentPath() == '/',
            )
        );
        for ($i = 0 ; $i < $fathers ; $i++) {
            if ($folders[$i] == '')
                continue;

            $link = '/';
            for ($j = 0 ; $j <= $i ; $j++)
                $link .= $folders[$j] . '/';

            $breadCrumb[] = array(
                'link' => $link,
                'label' => $folders[$i],
                'active' => $link == $this->getCurrentPath(),
            );
        }

        return $breadCrumb;
    }

    public function scanDirectoryContents()
    {
        try {
            $dirIterator = new DirectoryIterator($this->getFullPath());
        } catch(UnexpectedValueException $e) {
            return false;
        }

        $this->folderList = array();
        $this->fileList = array();

        foreach ($dirIterator as $fileInfo) {
            if ((strpos($fileInfo->getFilename(), '.') === 0 && $this->showHiddenFiles === false) || $fileInfo->isDot())
                continue;
            elseif ($fileInfo->isDir())
                $this->folderList[$fileInfo->getFilename()] = new FileInfo($fileInfo, $this->showFolderSize);
            else
                $this->fileList[$fileInfo->getFilename()] = new FileInfo($fileInfo, true);
        }

        ksort($this->folderList);
        ksort($this->fileList);

        return true;
    }

    public function isDownloadingFile()
    {
        return is_file(rtrim($this->getFullPath(), '/'));
    }

    public function downloadFile()
    {
        if (!$this->isDownloadingFile())
            return;

        $fullPath = rtrim($this->getFullPath(), '/');

        $fileInfo = new SplFileInfo($fullPath);
        header("Content-disposition: inline; filename=" . $fileInfo->getFileName());
        header("Content-type: ". mime_content_type($fullPath));
        readfile($fullPath);
    }

    public function isDirectoryEmpty()
    {
        return empty($this->folderList) && empty($this->fileList);
    }

    public function getDirectoryFolders()
    {
        return $this->folderList;
    }

    public function getDirectoryFiles()
    {
        return $this->fileList;
    }

    public function getCurrentPath()
    {
        return $this->_current;
    }

    public function getFullPath()
    {
        return $this->root . $this->getCurrentPath();
    }

    public function setShowHiddenFiles($showHiddenFiles = true)
    {
        $this->showHiddenFiles = $showHiddenFiles;
        return $this;
    }

    public function setShowFolderSize($showFolderSize = true)
    {
        $this->showFolderSize = $showFolderSize;
        return $this;
    }

    public function showFolderSize()
    {
        return $this->showFolderSize;
    }

    public function setShowLastModified($showLastModified = true)
    {
        $this->showLastModified = $showLastModified;
        return $this;
    }

    public function showLastModified()
    {
        return $this->showLastModified;
    }
}