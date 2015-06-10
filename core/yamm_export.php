<?php
use RA\OxidCleanupScripts\OxidCleanup;

/**
 * Controller that is used to export YAMM Configuration files for any Shop
 */
class yamm_export extends oxUBase {

    /**
     * Should the config be generated as config inherited by it's parent shop's config?
     * @var bool
     */
    private $_blInheritConfigFromParent = true;

    /**
     * Should the export generated aYAMMDisabledModules ?
     * @var bool
     */
    private $_blExportDisabledModules = true;

    /**
     * Should the Export generated aYAMMSpecialClassOrder ?
     * @var bool
     */
    private $_blExportClassOrder = true;

    /**
     * The way the user receives the generated config
     * download: Offers the file as a download
     * save: Saves and overwrites the existing config in the file system
     * echo: Displays the generated config in the browser
     * @var string
     */
    private $_sOutputType = 'download';

    /**
     * The generated Export data
     * @var array
     */
    private $_aExportOutput = array();

    /**
     * A list of errors (Exceptions) to be displayed
     * @var array
     */
    private $_aErrors = array();

    /**
     * A list of Shop IDs to generate the configs for
     * @var array
     */
    private $_aShopIds = array();

    /**
     * A parameter that can be set, to force configs to extend this parent
     * @var null
     */
    private $_iOverwriteParent = null;

   /**
     * Takes care of rendering the form in export view
     * @return null|string
     */
    public function render() {

        $this->addTplParam('sAction', $_SERVER['REQUEST_URI']);

        if ($this->getConfig()->getActiveShop()->oxshops__oxparentid->value !== 0) {
            $this->addTplParam('blHasParentShop', true);
        }

        $this->handleRequest();

        // Params passed to the exporter
        $this->addTplParam('blInheritConfigFromParent', $this->_blInheritConfigFromParent);
        $this->addTplParam('blExportDisabledModules', $this->_blExportDisabledModules);
        $this->addTplParam('blExportClassOrder', $this->_blExportClassOrder);

        // Params for use inside the controller
        $this->addTplParam('sOutputType', $this->_sOutputType);
        $this->addTplParam('aGeneratedOutput', $this->_aExportOutput);
        $this->addTplParam('aErrors', $this->_aErrors);
        $aShopIds = oxRegistry::getConfig()->getShopIds();
        sort($aShopIds);
        $this->addTplParam('aShopIds', $aShopIds);
        $this->addTplParam('aSelectedShopIds', $this->_aShopIds);
        $this->addTplParam('iOverwriteParent', $this->_iOverwriteParent);

        $sContext = oxRegistry::getConfig()->getShopConfVar('sYAMMContext');
        $this->addTplParam('sContext', $sContext !== null && $sContext !== '' ? $sContext : 'production');
        return 'yamm_export.tpl';
    }

    /**
     * Handles user input, given by the form in the export view
     */
    private function handleRequest()
    {
        if ($this->getConfig()->getRequestParameter('yamm_export')) {
            // Get all GET parameters and set corresponding field values
            $exportConfig = $this->getConfig()->getRequestParameter('yamm_export');
            foreach ($exportConfig as $key => $value) {
                if (property_exists($this, '_' . $key)) {
                    $this->{'_' . $key} = $value;
                }
            }

            try {
                $this->cleanUpModules();
            } catch(\Exception $exception) {
                $this->_aErrors[] = $exception;
                return false;
            }

            $yammExporter = oxNew(
                "yamm_exporter",
                $this->_blInheritConfigFromParent,
                $this->_blExportDisabledModules,
                $this->_blExportClassOrder,
                $this->_iOverwriteParent
            );

            if (!empty($this->_aShopIds)) {
                foreach ($this->_aShopIds as $sShopId) {
                    $this->_aExportOutput[$sShopId] = $yammExporter->export($sShopId);
                }
            } else {
                $this->_aExportOutput[oxRegistry::getConfig()->getShopId()] = $yammExporter->export(
                    oxRegistry::getConfig()->getShopId()
                );
            }

            if (!empty($this->_aExportOutput)) {
                switch ($this->_sOutputType) {
                    // Offer generated config file as file download
                    case "download":
                        $this->offerConfigAsDownloadableFile();
                        break;
                    // Save generated config straight into YAMM Config folder
                    case "save":
                        $this->saveConfigToFileSystem();
                        break;
                }
            }
        }
    }

    /**
     * Calls the cleanup service to clean up all of Oxid's module handling internal data
     */
    private function cleanUpModules() {
        $oCleanUpService = new OxidCleanup(getShopBasePath());

        $oCleanUpService->fullCleanUp();
    }

    /**
     * Saves the generated config to the filesystem into the YAMM folder
     * The generated config will immediatly be used by YAMM
     */
    private function saveConfigToFileSystem()
    {
        $sConfigPath = rtrim(getShopBasePath(), '/');

        foreach ($this->_aExportOutput as $sShopId => $sExportOutput) {
            $sYAMMConfigFile = $this->getConfigPath($sConfigPath, $sShopId);
            try {
                if (!is_dir(dirname($sYAMMConfigFile))) {
                    @mkdir(dirname($sYAMMConfigFile), 0775, true);
                    if (!is_dir(dirname($sYAMMConfigFile))) {
                        throw new \RuntimeException("Could not create directory " . dirname($sYAMMConfigFile) . ", check file permissions ");
                    }
                }
                file_put_contents($sYAMMConfigFile, $sExportOutput);
            } catch (\Exception $e) {
                $this->_aErrors[] = $e;
            }
        }
    }

    /**
     * Sets headers and ouputs the config file, so that the user can download it
     */
    private function offerConfigAsDownloadableFile()
    {

        $oUtils = oxRegistry::getUtils();
        $file = tempnam("tmp", "zip");

        $zip = new ZipArchive();

        // Zip will open and overwrite the file, rather than try to read it.
        $zip->open($file, ZipArchive::OVERWRITE);

        foreach ($this->_aExportOutput as $sShopid => $sExportOutput) {
            $zip->addFromString($this->getConfigPath('', $sShopid), $sExportOutput);
        }

        $zip->close();

        // Stream the file to the client
        $oUtils->setHeader("Content-Type: application/zip");
        $oUtils->setHeader("Content-Length: " . filesize($file));
        $oUtils->setHeader("Content-Disposition: attachment; filename=\"yamm.zip\"");
        $content = readfile($file);
        unlink($file);
        $oUtils->showMessageAndExit($content);

    }

    /**
     * @param $sConfigPath
     *
     * @return string
     */
    private function getConfigPath($sConfigPath, $sShopId = 'oxbaseshop')
    {
        if ($sConfigPath != '') {
            $sConfigPath .= '/';
        }
        $sConfigPath .= 'YAMM';
        if (oxRegistry::getConfig()->getShopConfVar('sYAMMContext') !== null) {
            $sYAMMContext = oxRegistry::getConfig()->getShopConfVar('sYAMMContext');
        } else {
            $sYAMMContext = 'production';
        }

        $sConfigPath .= '/' . $sYAMMContext;

        $bMultiShop = $sShopId !== 'oxbaseshop';
        if ($bMultiShop) {
            $sConfigPath .= '/' . $sShopId;
        }

        $sYAMMConfigFile = $sConfigPath . '/yamm.config.php';

        return $sYAMMConfigFile;
    }
}

