<?php
namespace Kernel\Asyn\Es;

use Kernel\Asyn\Es\Exceptions\UnsupportedFeatureException;
use Kernel\Asyn\Es\Builders\SelectStatementBuilder;
use Kernel\Asyn\Es\Builders\DeleteStatementBuilder;
use Kernel\Asyn\Es\Builders\UpdateStatementBuilder;
use Kernel\Asyn\Es\Builders\InsertStatementBuilder;
use Kernel\Asyn\Es\Builders\CreateStatementBuilder;
use Kernel\Asyn\Es\Builders\ShowStatementBuilder;

/**
 * This class generates SQL from the output of the PHPSQLParser.
 *
 * @author  André Rothe <andre.rothe@phosco.info>
 * @license http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 *
 */
class PHPSQLCreator
{

    public function __construct($parsed = false)
    {
        if ($parsed) {
            $this->create($parsed);
        }
    }

    public function create($parsed)
    {
        $k = key($parsed);
        switch ($k) {
            case "UNION":
            case "UNION ALL":
                throw new UnsupportedFeatureException($k);
            break;
            case "SELECT":
                $builder = new SelectStatementBuilder($parsed);
                $this->created = $builder->build($parsed);
                break;
            case "INSERT":
                $builder = new InsertStatementBuilder($parsed);
                $this->created = $builder->build($parsed);
                break;
            case "DELETE":
                $builder = new DeleteStatementBuilder($parsed);
                $this->created = $builder->build($parsed);
                break;
            case "UPDATE":
                $builder = new UpdateStatementBuilder($parsed);
                $this->created = $builder->build($parsed);
                break;
            case "RENAME":
                $this->created = $this->processRenameTableStatement($parsed);
                break;
            case "SHOW":
                $builder = new ShowStatementBuilder($parsed);
                $this->created = $builder->build($parsed);
                break;
            case "CREATE":
                $builder = new CreateStatementBuilder($parsed);
                $this->created = $builder->build($parsed);
                break;
            default:
                throw new UnsupportedFeatureException($k);
            break;
        }
        return $this->created;
    }

    // TODO: we should change that, there are multiple "rename objects" as
    // table, user, database
    protected function processRenameTableStatement($parsed)
    {
        $rename = $parsed['RENAME'];
        $sql = "";
        foreach ($rename as $k => $v) {
            $len = strlen($sql);
            $sql .= $this->processSourceAndDestTable($v);

            if ($len == strlen($sql)) {
                throw new UnableToCreateSQLException('RENAME', $k, $v, 'expr_type');
            }

            $sql .= ",";
        }
        $sql = substr($sql, 0, -1);
        return "RENAME TABLE " . $sql;
    }

    protected function processSourceAndDestTable($v)
    {
        if (!isset($v['source']) || !isset($v['destination'])) {
            return "";
        }
        return $v['source']['base_expr'] . " TO " . $v['destination']['base_expr'];
    }
}
