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

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\LangUtils;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\player\Player;
use pocketmine\Server;

use Vecnavium\FormsUI\CustomForm;

class EditkitPotionForm {

    public static function sendTo(Player $player, Kit $kit, int $potion) : void {
        if(isset($kit->getEffects()[$potion])) $kitPot = $kit->getEffects()[$potion];
        $effect = EffectIdMap::getInstance()->fromId($potion);
        $ui = new CustomForm(function (Player $player, $data) use ($kit, $potion, $effect) {
            if($data === null) {
                EditkitPotionSelectForm::sendTo($player, $kit);
                return;
            }
            if(!$data["enabled"]) {
                $effects = $kit->getEffects();
                if(isset($effects[$potion])) {
                    unset($effects[$potion]);
                    $kit->setEffects($effects);
                }
                $player->sendMessage(LangUtils::getMessage("editkit-potion-success-removed", true, [
                    "{POTION}" => Server::getInstance()->getLanguage()->translate($effect->getName()),
                    "{NAME}" => $kit->getName(),
                ]));
                return;
            }

            if(!is_int((int)$data["duration"]) || (int)$data["duration"] < 1) {
                $player->sendMessage(LangUtils::getMessage("editkit-potion-invalid-duration"));
                return;
            }
            if(!is_int((int)$data["amplifier"]) || (int)$data["amplifier"] < 1) {
                $player->sendMessage(LangUtils::getMessage("editkit-potion-invalid-amplifier"));
                return;
            }

            $instance = new EffectInstance($effect, (int)$data["duration"] * 20, (int)$data["amplifier"] - 1);

            $new = clone $kit;

            $eff = $new->getEffects();
            $eff[$potion] = $instance;
            $new->setEffects($eff);

            if(KitManager::update($kit, $new)) {
                KitManager::saveAll();

                $player->sendMessage(LangUtils::getMessage("editkit-potion-success-added", true, [
                    "{POTION}" => Server::getInstance()->getLanguage()->translate($effect->getName()),
                    "{NAME}" => $kit->getName(),
                ]));
            }
            EditkitPotionSelectForm::sendTo($player, KitManager::get($kit->getName()));
        });
        $ui->setTitle(LangUtils::getMessage("editkit-title"));
        $ui->addLabel(LangUtils::getMessage("editkit-potion-text", true, [
            "{POTION}" => Server::getInstance()->getLanguage()->translate($effect->getName()),
            "{NAME}" => $kit->getName(),
        ]));

        $ui->addToggle(LangUtils::getMessage("editkit-potion-toggle"), isset($kitPot), "enabled");
        $ui->addInput(LangUtils::getMessage("editkit-potion-duration"), "", isset($kitPot) ? (string)($kitPot->getDuration() / 20) : "10", "duration");
        $ui->addInput(LangUtils::getMessage("editkit-potion-amplifier"), "", isset($kitPot) ? (string)($kitPot->getAmplifier() + 1) : "1", "amplifier");

        $player->sendForm($ui);
    }

}