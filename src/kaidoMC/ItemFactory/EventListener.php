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

use kaidoMC\ItemFactory\Loader;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;

use pocketmine\command\ConsoleCommandSender;

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class EventListener implements Listener
{
    /**
    * @var Loader $plugin
    */
    private $plugin;
    
    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
    }
    
    /**
     * @return Loader
     */
    public function getPlugin(): Loader
    {
        return $this->plugin;
    }
    
    public function onTouch(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        if($item->getNamedTag()->hasTag("KND", StringTag::class)) {
            $sub_str = str_replace("{player}", $player->getName(), $item->getNamedTag()->getString("KND"));
            $this->getPlugin()->getServer()->dispatchCommand(new ConsoleCommandSender(), $sub_str);
        }
    }
    
    public function onItem(PlayerDropItemEvent $event): void
    {
        if($event->getItem()->getNamedTag()->hasTag("canAction", StringTag::class)) {
            $event->setCancelled(true);
        }
    }
}