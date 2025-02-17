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

use pocketmine\player\Player;

use AndreasHGK\EasyKits\Kit;

class CooldownManager{

    /** @var self */
    public static $instance = null;

    /**
     * @var int[][]
     */
    public array $cooldowns = [];

    /**
     * Check if the player has an active cooldown for the given kit
     *
     * @param Kit $kit
     * @param Player $player
     * @return bool
     */
    public static function hasKitCooldown(Kit $kit, Player $player): bool{
        $kitCooldowns = self::getCooldowns();
        if(isset($kitCooldowns[$kit->getName()][$player->getName()])) {
            if($kitCooldowns[$kit->getName()][$player->getName()] + $kit->getCooldown() > time()) {
                return true;
            }
            self::unsetKitCooldown($kit, $player);
            self::saveCooldowns();
        }
        return false;
    }

    /**
     * Check how long the cooldown for a player is for a given kit
     *
     * @param Kit $kit
     * @param Player $player
     * @return int
     */
    public static function getKitCooldown(Kit $kit, Player $player): int{
    $kitCooldowns = self::getCooldowns();
    if(!self::hasKitCooldown($kit, $player)) return 0;
    return (int) floor(($kitCooldowns[$kit->getName()][$player->getName()] + $kit->getCooldown()) - time());
}

    /**
     * Change the cooldown that a player has for a kit
     *
     * @param Kit $kit
     * @param Player $player
     */
    public static function setKitCooldown(Kit $kit, Player $player): void{
        self::getInstance()->cooldowns[$kit->getName()][$player->getName()] = time();
        self::saveCooldowns();
    }

    /**
     * Remove the cooldown a player has for a kit
     *
     * @param Kit $kit
     * @param Player $player
     */
    public static function unsetKitCooldown(Kit $kit, Player $player): void{
        unset(self::getInstance()->cooldowns[$kit->getName()][$player->getName()]);
        self::saveCooldowns();
    }


    /**
     * Get all current cooldowns
     *
     * @return array
     * @internal
     */
    public static function getCooldowns(): array{
        return self::getInstance()->cooldowns;
    }

    /**
     * @internal
     */
    public static function loadCooldowns(): void{
        self::getInstance()->cooldowns = DataManager::get(DataManager::COOLDOWN)->getAll();
    }

    /**
     * @internal
     */
    public static function saveCooldowns(): void{
        DataManager::get(DataManager::COOLDOWN)->setAll(self::getCooldowns());
        DataManager::save(DataManager::COOLDOWN);
    }

    private function __construct(){
    }

    public static function getInstance(): self{
        if(is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

}
