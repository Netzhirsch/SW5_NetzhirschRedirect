<?php

namespace NetzhirschRedirect;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Exception;
use Shopware\Components\Model\ModelManager;
use NetzhirschRedirect\Models\LocationByIP\LocationByIP;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin;

class NetzhirschRedirect extends Plugin {

    public function install(InstallContext $context)
    {
        $schemaManager = $this->container->get('dbal_connection')->getSchemaManager();
        if ($schemaManager->tablesExist(['s_plugin_netzhirschRedirect_locationByIp']))
            return;

        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);
        $classes = [$modelManager->getClassMetadata(LocationByIP::class)];
        $logger = $this->container->get('pluginlogger');
        try {
            $tool->createSchema($classes);
            try {
			    $this->readCsv($modelManager);
            } catch (Exception $exception) {
                $logger->error($exception->getMessage());
            }
        } catch (ToolsException $exception) {
            $logger->error($exception->getMessage());
        }
    }

    public function uninstall(UninstallContext $context)
    {
        if ($context->keepUserData())
            return;

        $em = $this->container->get('models');
        $tool = new SchemaTool($em);
        $classes = [$em->getClassMetadata(LocationByIP::class)];
        $tool->dropSchema($classes);
        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);

    }

    public function activate(ActivateContext $context) {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
	}

	public function deactivate(DeactivateContext $context) {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
	}

	public function update(UpdateContext $context) {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
	}

    /**
     * @param ModelManager $modelManager
     * @throws Exception
     */
    private function readCsv(
        ModelManager $modelManager
    )
    {

        $path = dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            .'NetzhirschRedirect'
            . DIRECTORY_SEPARATOR
            .'geoip2-ipv4_csv.csv'
        ;

        $file_handle = fopen($path, "r");

        if (!$file_handle)
           return;


        $data = $this->getDataFormLine($file_handle);
        foreach ($data as $index => $locations) {
            $locationByIP = new LocationByIP();
            $locationByIP->setIpFrom($locations['ipFrom']);
            $locationByIP->setIpTo($locations['ipTo']);
            $locationByIP->setCountryCode($locations['countryCode']);
            $locationByIP->setCountryName($locations['countryName']);
            $modelManager->persist($locationByIP);
            if ($index % 10000 == 0)
                $modelManager->flush();
        }
        try {
            $modelManager->flush();
        } catch (OptimisticLockException $e) {
        }
    }

    private function getDataFormLine($file_handle){
        $data = [];
        $lineNumber = 0;
        while (($line = fgetcsv($file_handle, 3000, ",")) !== false) {

            $lineNumber++;

            if ($lineNumber == 1)
                continue;

            if (!isset($line[4]) || empty($line[4])) {
                continue;
            }
            $countryCode = $line[4];

            if (!isset($line[5]) || empty($line[5])) {
                continue;
            }
            $countryName = $line[5];

            if (!isset($line[0])) {
                continue;
            }

            $idRange = explode('/', $line[0]);
            if (!is_array($idRange)) {
                continue;
            }

            $ipFrom = $idRange[0];

            $ipParts = explode('.', $ipFrom);
            if (
                !is_array($ipParts)
                || !isset($ipParts[0])
                || !isset($ipParts[1])
                || !isset($ipParts[2])
            ) {
                continue;
            }

            $ipTo = $ipParts[0].'.'.$ipParts[1].'.'.$ipParts[2].'.'.$idRange[1];

            $data[$lineNumber] = [
                'countryCode' => $countryCode,
                'countryName' => $countryName,
                'ipFrom' => $ipFrom,
                'ipTo' => $ipTo,
            ];
        }
        return $data;
    }
}
