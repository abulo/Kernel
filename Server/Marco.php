<?php

namespace Kernel\Server;

class Marco
{
    /**
     * 分布式模式
     */
    const CLUSTER                                   = 0;
    /**
     * 主从模式
     */
    const MASTER_SLAVE                              = 1;
    /**
     * ASYN
     */
    const MSG_TYPR_ASYN = 9000;

    /**
     * 不进行序列化
     */
    const SERIALIZE_NONE                            = 0;
    /**
     * PHP serialize
     */
    const SERIALIZE_PHP                             = 1;
    /**
     * PHP IGBINARY
     */
    const SERIALIZE_IGBINARY                        = 2;
    /**
     * 进程为WORKER
     */
    const PROCESS_WORKER                            = 1;
    /**
     * 进程为TASKER
     */
    const PROCESS_TASKER                            = 2;
    /**
     * 进程为RELOAD
     */
    const PROCESS_RELOAD                            = 3;
    /**
     * 进程为CONFIG
     */
    const PROCESS_CONFIG                            = 4;
    /**
     * 进程为TIMER
     */
    const PROCESS_TIMER                             = 5;
    /**
     * 进程为MASTER
     */
    const PROCESS_MASTER                            = 4094;
    /**
     * 进程为MANAGER
     */
    const PROCESS_MANAGER                           = 4095;
    /**
     * 进程为USER（默认）
     */
    const PROCESS_USER                              = 4096;
    /**
     * 进程名称
     */
    const PROCESS_NAME                              = [
        self::PROCESS_MASTER                        => 'Master',
        self::PROCESS_MANAGER                       => 'Manager',
        self::PROCESS_WORKER                        => 'Worker',
        self::PROCESS_TASKER                        => 'Tasker',
        self::PROCESS_RELOAD                        => 'Reload',
        self::PROCESS_CONFIG                        => 'Config',
        self::PROCESS_TIMER                         => 'Timer',
        self::PROCESS_USER                          => 'User',
    ];
}
