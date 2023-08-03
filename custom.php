<?php

namespace AppBundle\Custom\ProxyQueries;

use ToolsBundle\DbSimple\Dbs;
use WebBundle\Custom\DataTables\DataTablesManager;
use WebBundle\Custom\DataTables\DataTablesQuery;

class ProxyQueriesManager
{
    /** @var Dbs */
    private $mainDb;
    /** @var DataTablesManager */
    private $dataTablesManager;

    /**
     * @param Dbs               $mainDb
     * @param DataTablesManager $dataTablesManager
     */
    public function __construct(Dbs $mainDb, DataTablesManager $dataTablesManager)
    {
        $this->mainDb = $mainDb;
        $this->dataTablesManager = $dataTablesManager;
    }

    /**
     * @param DataTablesQuery $dataTablesQuery
     * @param array           $settings
     * @return array
     * @throws \Exception
     */
    public function getTable(DataTablesQuery $dataTablesQuery, array $settings)
    {
        $sql = "SELECT datetime, a.user, query, server, duration, from_cache, count(*) OVER() AS full_count 
            FROM proxy_queries a
            WHERE *WHERE*
            ORDER BY *ORDER*
            LIMIT *LIMITS*
        ";
        // handle order
        $items = [];
        foreach ($dataTablesQuery->getOrders() as $order) {
            $columnId = $order->getColumnId();
            switch ($columnId) {
                case 'datetime':
                case 'user':
                case 'query':
                case 'server':
                case 'duration':
                case 'from_cache':
                    $items[] = $columnId . ' ' . $order->getDirection();
                    break;
                default:
                    throw new \Exception("Unexpected sort field: " . $columnId);
            }
        }

        if ($items) {
            $sql = str_replace('*ORDER*', implode(', ', $items), $sql);
        } else {
            $sql = str_replace('ORDER BY *ORDER*', '', $sql);
        }

        // handle limits
        if ($dataTablesQuery->getLength() >= 0) {
            $limits = $dataTablesQuery->getLength() . ' OFFSET ' . $dataTablesQuery->getStart();
            $sql = str_replace('*LIMITS*', $limits, $sql);
        } else {
            $sql = str_replace('LIMIT *LIMITS*', '', $sql);
        }
        $where = [];

        if ($settings['from_cache'] === 'any') {
            unset($settings['from_cache']);
        }

        foreach ($settings as $key => $item) {
            if (isset($item) && $item !== '') {
                    switch ($key) {
                        case 'from_cache':
                            $where[] = $key . " = '" . intval($item) . "'";
                            break;
                        case 'server':
                            $where[] = $key . " = '" . $item . "'";
                            break;
                        case 'query':
                            $where[] = $key . " ILIKE '%" . str_replace("'", "''", $item) . "%'";
                            break;
                        case 'user':
                            $where[] = "a." . $key . " = '" . $item . "'";
                            break;
                        case 'datetime':
                            $period = explode(' - ', $item);
                            if (count($period) != 2) {
                                throw new \Exception("Cant find period");
                            }
                            $dateFrom = date("Y-m-d", strtotime($period[0]));
                            $dateTo = date("Y-m-d", strtotime($period[1]));
                            $where[] = "datetime >= '" . $dateFrom . "' AND datetime <= '" . $dateTo . "'";
                            break;
                    }
                }
        }
        if ($where) {
            $sql = str_replace('*WHERE*', implode(' AND ', $where), $sql);
        } else {
            $sql = str_replace('WHERE *WHERE*', '', $sql);
        }
        $data = $this->mainDb->select($sql) ?: [];

        $totalRows = reset($data)['full_count'] ?? 0;

        return $this->dataTablesManager->createResponse($dataTablesQuery, $data, $totalRows);
    }

    /**
     * @return array
     */
    public function getServers()
    {
        return $this->mainDb->select("
            SELECT DISTINCT server from proxy_queries
        ") ?: [];
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->mainDb->select("
            SELECT DISTINCT a.user from proxy_queries a
        ") ?: [];
    }
}
