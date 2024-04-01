<?php
/**
 *    _____                         _  __  _   _         
 *   | ____|   __ _   ___   _   _  | |/ / (_) | |_   ___ 
 *   |  _|    / _` | / __| | | | | | ' /  | | | __| / __|
 *   | |___  | (_| | \__ \ | |_| | | . \  | | | |_  \__ \
 *   |_____|  \__,_| |___/  \__, | |_|\_\ |_|  \__| |___/
 *                           |___/                        
 *          by AndreasHGK and fernanACM 
 */
declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use pocketmine\Server;

use pocketmine\plugin\PluginBase;

use pocketmine\command\PluginCommand;

use AndreasHGK\EasyKits\command\CreatecategoryCommand;
use AndreasHGK\EasyKits\command\CreatekitCommand;
use AndreasHGK\EasyKits\command\DeletecategoryCommand;
use AndreasHGK\EasyKits\command\DeletekitCommand;
use AndreasHGK\EasyKits\command\EditkitCommand;
use AndreasHGK\EasyKits\command\EKImportCommand;
use AndreasHGK\EasyKits\command\GivekitCommand;
use AndreasHGK\EasyKits\command\KitCommand;
use AndreasHGK\EasyKits\customenchants\PiggyCustomEnchantsLoader;
use AndreasHGK\EasyKits\listener\InteractClaimListener;
use AndreasHGK\EasyKits\manager\CategoryManager;
use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\EconomyManager;
use AndreasHGK\EasyKits\manager\KitManager;

class EasyKits extends PluginBase{

    public const PERM_ROOT = "easykits.";

    /** @var EasyKits $instance */
    protected static EasyKits $instance;

    /**
     * @return void
     */
    public function onLoad(): void{
        self::$instance = $this;
        DataManager::loadFiles();
        PiggyCustomEnchantsLoader::load();
        CooldownManager::loadCooldowns();
        if(!EconomyManager::isEconomyLoaded()) $this->getLogger()->notice("no compatible economy loaded");
    }

    /**
     * @return void
     */
    public function onEnable(): void{
        DataManager::loadCheck();
        DataManager::loadVirions();
        EconomyManager::loadEconomy();
        $this->loadKits();
        $this->loadCommands();
        $this->loadEvents();
        $this->getLogger()->info("
 _____                         _  __  _   _             _____   ___  __  __  _____   ____  
| ____|   __ _   ___   _   _  | |/ / (_) | |_   ___    |  ___| |_ _| \ \/ / | ____| |  _ \ 
|  _|    / _` | / __| | | | | | ' /  | | | __| / __|   | |_     | |   \  /  |  _|   | | | |
| |___  | (_| | \__ \ | |_| | | . \  | | | |_  \__ \   |  _|    | |   /  \  | |___  | |_| |
|_____|  \__,_| |___/  \__, | |_|\_\ |_|  \__| |___/   |_|     |___| /_/\_\ |_____| |____/ 
                        |___/           
        by fernanACM & AndreasHGK | https://github.com/fernanACM                                                    
           ");
    }

    /**
     * @return void
     */
    public function onDisable(): void{
        KitManager::saveAll();
        CooldownManager::saveCooldowns();
        CategoryManager::saveAll();
    }

    /**
     * @return void
     */
    private function loadCommands(): void{
        $commands = [
            new CreatekitCommand(),
            new DeletekitCommand(),
            new EditkitCommand(),
            //new EKImportCommand(),
            new KitCommand(),
            new GivekitCommand(),
        ];
        if(DataManager::getKey(DataManager::CONFIG, "enable-categories")){
            array_push($commands,
                new CreatecategoryCommand(),
                new DeletecategoryCommand()
            );
        }
        foreach($commands as $command){
            $cmd = new PluginCommand($command->getName(), $this, $command);
            $cmd->setDescription($command->getDesc());
            $cmd->setAliases($command->getAliases());
            $cmd->setPermission($command->getPermission());
            $cmd->setUsage($command->getUsage());
            Server::getInstance()->getCommandMap()->register("easykits", $cmd);
        }
    }

    /**
     * @return void
     */
    private function loadEvents(): void{
        $listeners = [
            new InteractClaimListener(),
        ];
        foreach($listeners as $listener) {
            Server::getInstance()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    /**
     * @return void
     */
    private function loadKits(): void{
        if(!PiggyCustomEnchantsLoader::isPluginLoaded()){
            KitManager::loadAll();
            if(DataManager::getKey(DataManager::CONFIG, "enable-categories")){
                CategoryManager::loadAll();
            }
        }else{
            KitManager::loadAll();
            if(DataManager::getKey(DataManager::CONFIG, "enable-categories")){
                CategoryManager::loadAll();
            }
        }
    }

    /**
     * @return self
     */
    public static function get(): self{
        return self::$instance;
    }
}
