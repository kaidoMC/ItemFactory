<?php

/**
 *  _         _     _       __  __  ____ 
 * | | ____ _(_) __| | ___ |  \/  |/ ___|
 * | |/ / _` | |/ _` |/ _ \| |\/| | |    
 * |   < (_| | | (_| | (_) | |  | | |___ 
 * |_|\_\__,_|_|\__,_|\___/|_|  |_|\____|
 */     

declare(strict_types = 1);

namespace kaidoMC\ItemFactory;

use kaidoMC\ItemFactory\FormSystem;
use kaidoMC\ItemFactory\EventListener;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

use kaidoMC\ItemFactory\libs\jojoe77777\FormAPI\SimpleForm;
use kaidoMC\ItemFactory\libs\jojoe77777\FormAPI\CustomForm;

class Loader extends PluginBase
{
    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if($command->getName() == "ifa") {
            if(!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "Please use this command in the game!");
            } else {
                $fSystem = new FormSystem();
                $fSystem->getForm($sender);
            }
        }
        return true;
    }
}