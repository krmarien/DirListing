<?php
require('DirListing.php');

$dirListing = new DirListing($_SERVER['FILES_ROOT']);
$dirListing->setShowHiddenFiles((bool) $_SERVER['SHOW_HIDDEN_FILES']);
$dirListing->setShowLastModified((bool) $_SERVER['SHOW_MODIFIED_DATE']);
$dirListing->setShowFolderSize((bool) $_SERVER['SHOW_FOLDER_SIZE']);

if ($dirListing->isDownloadingFile()) {
    $dirListing->downloadFile();
    exit;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <!-- Bootstrap -->
        <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
           <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <!-- Icon -->
        <link rel="icon" href="/favicon32.ico">
        <title><?= $dirListing->getCurrentPath() ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style type="text/css">
            body {
                padding-top: 60px;
            }
        </style>
    </head>

    <body>
        <div class="container navbar-fixed-top">
            <ol class="breadcrumb" style="margin-top: 5px">
                <?php
                $breadCrumb = $dirListing->getBreadCrumb();
                foreach($breadCrumb as $item):
                    if ($item['active']):
                        ?>
                            <li class="active"><?= $item['label'] ?></li>
                        <?php
                    else:
                        ?>
                            <li><a href="<?= $item['link'] ?>"><?= $item['label'] ?></a></li>
                        <?php
                    endif;
                endforeach;
                ?>
            </ol>
        </div>

        <div class="container">
            <?php
            if (!$dirListing->scanDirectoryContents()):
                ?>
                <div class="alert alert-danger text-center"><strong>Error!</strong> failed to open folder </div>
                <?php
            elseif ($dirListing->isDirectoryEmpty()):
                ?>
                <div class="alert alert-info text-center"><strong>This folder is empty</strong></div>
                <?php
            else:
                ?>
                <table class="table table-condensed table-hover">
                    <thead>
                        <th width="35"></th>
                        <th class="text-primary">Name</th>
                        <th width="89" class="text-primary text-center">Size</th>
                        <?php if ($dirListing->showLastModified()): ?>
                            <th class="text-primary text-center">Last Modified</th>
                        <?php endif; ?>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($dirListing->getDirectoryFolders() as $folderInfo):
                        ?>
                            <tr>
                                <td><span class="glyphicon glyphicon-folder-open"></span></td>
                                <td><a href="<?= $dirListing->getCurrentPath() . $folderInfo->getFileName() ?>"><?= $folderInfo->getFileName() ?></td>
                                <td class="text-center"><?= $dirListing->showFolderSize() ? $folderInfo->getSizeFormatted() : '-' ?></td>
                                <?php if ($dirListing->showLastModified()): ?>
                                    <td class="text-center"><small><?= $folderInfo->getModifiedDate()->format('d/m/y H:i:s') ?></small></td>
                                <?php endif; ?>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                        <?php
                        foreach ($dirListing->getDirectoryFiles() as $fileInfo):
                        ?>
                            <tr>
                                <td><span class="glyphicon <?= $fileInfo->getIcon() ?>"></span></td>
                                <td><a href="<?= $dirListing->getCurrentPath() . $fileInfo->getFileName() ?>"><?= $fileInfo->getFileName() ?></td>
                                <td class="text-center"><?= $fileInfo->getSizeFormatted() ?></td>
                                <?php if ($dirListing->showLastModified()): ?>
                                    <td class="text-center"><small><?= $fileInfo->getModifiedDate()->format('d/m/y H:i:s') ?></small></td>
                                <?php endif; ?>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    </tbody>
                </table>
                <?php
            endif;
            ?>
        </div>

        <script src="http://code.jquery.com/jquery.js"></script>
        <script src="/bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>