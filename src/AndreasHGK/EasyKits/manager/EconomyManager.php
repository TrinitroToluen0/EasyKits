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

namespace AndreasHGK\EasyKits\manager;

use pocketmine\Server;
use pocketmine\player\Player;

use AndreasHGK\EasyKits\EasyKits;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;

use onebone\economyapi\EconomyAPI;
use Twisted\MultiEconomy\MultiEconomy;

class EconomyManager{

    /**
     * @var null|EconomyAPI|BedrockEconomy
     */
    public static $economy = null;

    /**
     * @param Player $player
     * @return float
     */
    public static function getMoney(Player $player): float
    {
        self::loadEconomy();
        $economy = self::getEconomy();
        EasyKits::get()->getLogger()->info(get_class($economy) . "\n");
        switch (true) {
            case $economy instanceof EconomyAPI:
                return $economy->myMoney($player);
            case $economy instanceof MultiEconomy:
                $currency = DataManager::getKey(DataManager::CONFIG, "multieconomy-currency");
                return $economy->getAPI()->getBalance($player->getName(), $currency);
            case $economy instanceof BedrockEconomy:
                BedrockEconomyAPI::CLOSURE()->get(
                    xuid: $player->getXuid(),
                    username: $player->getName(),
                    onSuccess: function (array $result) {
                        EasyKits::get()->getLogger()->info($result["amount"] . "\n");
                        return $result["amount"];
                    },
                    onError: static function (): void {}
                );
        }
        EasyKits::get()->getLogger()->info("NO INSTANCE \n");
        return 0;
    }


    /**
     * @param Player $player
     * @param float $money
     * @param boolean $force
     * @return void
     */
    public static function setMoney(Player $player, float $money, bool $force = false): void
    {
        $economy = self::getEconomy();
        switch (true) {
            case $economy instanceof EconomyAPI:
                $economy->setMoney($player, $money);
                break;
            case $economy instanceof MultiEconomy:
                $economy->getAPI()->setBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
            case $economy instanceof BedrockEconomy:
                BedrockEconomyAPI::CLOSURE()->set(
                    xuid: $player->getXuid(),
                    username: $player->getName(),
                    amount: (int) $money,
                    decimals: 0,
                    onSuccess: static function (): void {},
                    onError: static function (): void {}
                );
                break;
        }
    }

    /**
     * @param Player $player
     * @param float $money
     * @param boolean $force
     * @return void
     */
    public static function reduceMoney(Player $player, float $money, bool $force = false): void
    {
        $economy = self::getEconomy();
        switch (true) {
            case $economy instanceof EconomyAPI:
                $economy->reduceMoney($player, $money, $force);
                break;
            case $economy instanceof MultiEconomy:
                $economy->getAPI()->takeFromBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
            case $economy instanceof BedrockEconomy:
                BedrockEconomyAPI::CLOSURE()->subtract(
                    xuid: $player->getXuid(),
                    username: $player->getName(),
                    amount: (int) $money,
                    decimals: 0,
                    onSuccess: static function (): void {},
                    onError: static function (): void {}
                );
                break;
        }
    }

    /**
     * @param Player $player
     * @param float $money
     * @param boolean $force
     * @return void
     */
    public static function addMoney(Player $player, float $money, bool $force = false): void
    {
        $economy = self::getEconomy();
        switch (true) {
            case $economy instanceof EconomyAPI:
                $economy->addMoney($player, $money, $force);
                break;
            case $economy instanceof MultiEconomy:
                $economy->getAPI()->addToBalance($player->getName(), DataManager::getKey(DataManager::CONFIG, "multieconomy-currency"), $money);
                break;
            case $economy instanceof BedrockEconomy:
                BedrockEconomyAPI::CLOSURE()->add(
                    xuid: $player->getXuid(),
                    username: $player->getName(),
                    amount: (int) $money,
                    decimals: 0,
                    onSuccess: static function (): void {},
                    onError: static function (): void {}
                );
                break;
        }
    }

    /**
     * @return void
     */
    public static function loadEconomy(): void{
        $plugins = Server::getInstance()->getPluginManager();
        $economyAPI = $plugins->getPlugin("EconomyAPI");
        if($economyAPI instanceof EconomyAPI) {
            self::$economy = $economyAPI;
            EasyKits::get()->getLogger()->info("loaded EconomyAPI");
            return;
        }
        $bedrockEconomy = $plugins->getPlugin("BedrockEconomy");
        if($bedrockEconomy instanceof BedrockEconomy){
            self::$economy = $bedrockEconomy;
            EasyKits::get()->getLogger()->info("loaded BedrockEconomy");
            return;
        }
    }

    /**
     * @return boolean
     */
    public static function isEconomyLoaded(): bool{
        return self::getEconomy() !== null;
    }

    public static function getEconomy(){
        return self::$economy;
    }
}