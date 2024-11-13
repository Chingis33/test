<?php

namespace AppBundle\Command\Ssp;

use AppBundle\Command\CommandAbstract;
use Symfony\Component\Console\Input\InputArgument;

class TransferringAdvertisersBetweenAccountsCommand extends CommandAbstract
{
    protected function configure()
    {
        $this->setName('app:ssp:transferring:advertisers:between:accounts')
            ->setDescription('Transferring advertisers between accounts')
            ->addArgument('oldManager', InputArgument::REQUIRED, 'old account manager id ')
            ->addArgument('newManager', InputArgument::REQUIRED, 'new account manager id ')
            ->addArgument('reportcsv', InputArgument::OPTIONAL, 'Filename for full csv report');
    }

    protected function job()
    {
        $oldManager = $this->getArgument('oldManager');
        $newManager = $this->getArgument('newManager');
        $outputCsvFilename = $this->getArgument('reportcsv');

        if (!$oldManager || !$newManager) {
            throw new \Exception("please enter oldManagerId and newManagerId");
        }

        $this->stdoutLogger->start();
        $this->stdoutLogger->action("Getting advertisers with a old manager...");
        $advertisersByOldManager = $this->getContainer()->get('dbs.read')->selectCol("
            SELECT advertiser FROM gawt_advertisers_manager_account WHERE manager = ?d AND date_to IS NULL;
        ", (int)$oldManager) ?: [];

        if ($outputCsvFilename) {
            $this->stdoutLogger->action("Write advertisers to " . $outputCsvFilename);
            $fp = fopen($outputCsvFilename, "w");
            fputcsv($fp, $advertisersByOldManager, PHP_EOL);
            fclose($fp);
        }

        $request = [
            'managerId' => (int)$newManager,
            'dateFrom'  => date("Y-m-d", strtotime("now")),
        ];
        $this->stdoutLogger->action("Change to new account manager...");
        foreach ($advertisersByOldManager as $advertiser) {
            try {
                $this->getContainer()->get('app.gawt_api')
                    ->request('advertisers/' . $advertiser . '/account', 'post', $request);
            } catch (\Exception $e) {
                $this->stdoutLogger->pure("Error: " . $e->getMessage());
            }
        }
    }
}