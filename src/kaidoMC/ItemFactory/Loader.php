<?php

declare(strict_types = 1);

namespace kaidoMC\ItemFactory;

use pocketmine\Player;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

use pocketmine\utils\TextFormat;

use pocketmine\plugin\PluginBase;

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\convert\ItemTranslator;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use kaidoMC\ItemFactory\libs\jojoe77777\FormAPI\SimpleForm;
use kaidoMC\ItemFactory\libs\jojoe77777\FormAPI\CustomForm;


class Loader extends PluginBase
{

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{
		if($command->getName() == "itemfactory") {
			if(!$sender instanceof Player) {
				$sender->sendMessage(TextFormat::RED . "Please use this command in the game!");
			} else {
				$this->onForm($sender);
			}
		}
		return true;
	}

	/**
	 * @param Player $sender
	 */

	public function onForm(Player $sender): void
	{
		$nForm = new SimpleForm(function (Player $sender, ?int $result) {
			if($result === null) {
				return;
			}
			switch($result) {
				case 0:
					$this->createItem($sender);
				break;
				case 1:
					$this->cloneItem($sender);
				break;
				case 2:
					$this->onEdit($sender);
				break;
			}
		});
		$nForm->setTitle("ItemFactory");
		$nForm->addButton("Create");
		$nForm->addButton("Clone");
		$nForm->addButton("Edit");
		$sender->sendForm($nForm);
	}

	/**
	 * @param Player $sender
	 */

	public function createItem(Player $sender): void
	{
		$nForm = new CustomForm(function (Player $sender, ?array $result) {
			if($result === null) {
				$this->onForm($sender);
				return;
			}
			$itemId = explode(":", $result[0]);
			if(!is_numeric($itemId[0]) or !is_numeric($itemId[1])) {
				$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
				return;
			}

			if(!is_numeric($result[1]) or (int) $result[1] < 1) {
				$sender->sendMessage(TextFormat::RED . "Wrong format try again or the number of items in trouble.");
				return;
			}

			try {
				$item = ItemFactory::fromStringSingle($itemId[0]);
				ItemTranslator::getInstance()->toNetworkId((int) $itemId[0], (int) $itemId[1]);
			} catch (\InvalidArgumentException $e) {
				$sender->sendMessage(TextFormat::RED . "The selected Item ID does not exist.");
				return;
			}

			$item->setDamage((int) $itemId[1]);
			$item->setCount((int) $result[1]);

			if($result[2] !== "false") {
				$item->setCustomName($result[2]);
			}
			if($result[3] !== "false") {
				$item->setLore(explode(",", $result[3]));
			}

			if($result[5] !== "false") {
				foreach(explode(",", $result[5]) as $sEnc) {
					$encId = explode(":", $sEnc);
					if(!is_numeric($encId[0]) or !is_numeric($encId[1])) {
						continue;
					}
					$nEnchant = new EnchantmentInstance(Enchantment::getEnchantment((int)$encId[0]));
					$item->addEnchantment($nEnchant->setLevel((int)$encId[1]));
				}
			}

			if($result[6] !== "false") {
				foreach(explode(",", $result[6]) as $tag) {
					$sTag = explode(":", $tag);
					if(!is_numeric($sTag[0])) {
						continue;
					}
					switch($sTag[0]) {
						case "0":
							if(!is_string($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->setNamedTagEntry(new StringTag($sTag[1], $sTag[2]));
						break;
						case "1":
							if(!is_numeric($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->setNamedTagEntry(new IntTag($sTag[1], (int)$sTag[2]));
						break;
						default:
							$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
						break;
					}
				}
			}
			$sender->getInventory()->addItem($item);
			$sender->sendMessage(TextFormat::GREEN  . "Item has been successfully initialized.");
		});
		$nForm->setTitle("Create");
		$nForm->addInput("ID & Meta (Example: 1:0)");
		$nForm->addInput("Count", "1", "1");
		$nForm->addInput("CustomName", "false", "false");
		$nForm->addInput("Lore", "false", "false");
		$nForm->addLabel("* If you want to add an enchant array then put a comma after each id.");
		$nForm->addInput("ID Enchantment & Level (Example: 9:1)", "false", "false");
		$nForm->addInput("CompoundTag (Example: 0:", "false", "false");
		$nForm->addLabel("* Plugin can only support StringTag, IntTag.");
		$nForm->addLabel("- 0:string:string is for StringTag");
		$nForm->addLabel("- 1:string:int for IntTag");
		$nForm->addLabel("* You can also put an array of CompundTag when you put a comma after each Tag.");
		$sender->sendForm($nForm);
	}

	/**
	 * @param Player $sender
	 */

	public function cloneItem(Player $sender): void
	{
		$item = $sender->getInventory()->getItemInHand();
		if($item->getId() === 0) {
			$sender->sendMessage(TextFormat::RED . "Can't be done if you don't have the item on hand.");
			return;
		}

		$inv = $sender->getInventory();
		if($inv->canAddItem($item)){
			$sender->getInventory()->addItem($item);
			$sender->sendMessage(TextFormat::GREEN  . "The item in your hand has been cloned and added to your inventory.");
		} else {
			$sender->sendMessage(TextFormat::RED . "Your inventory doesn't have enough space to add items.");
		}
	}

	/**
	 * @param Player $sender
	 */

	public function onEdit(Player $sender): void
	{
		$item = $sender->getInventory()->getItemInHand();
		if($item->getId() === 0) {
			$sender->sendMessage(TextFormat::RED . "Can't be done if you don't have the item on hand.");
			return;
		}
		$nForm = new CustomForm (function (Player $sender, ?array $result) use ($item) {
			if($result === null) {
				$this->onForm($sender);
				return;
			}
			if($item->getName() !== $result[0]) {
				$item->setCustomName($result[0]);
			}
			if(join("\n", $item->getLore()) !== $result[1]) {
				$item->setLore(explode(",", $result[1]));
			}
			if(is_numeric($result[2])) {
				if($item->hasEnchantment((int) $result[2])) {
					$item->removeEnchantment((int) $result[2]);
				}
			}
			if($result[3] !== "false") {
				if($item->getNamedTag()->hasTag($result[3])) {
					$item->getNamedTag()->removeTag($result[3]);
				}
			}
			if($result[4] !== "false") {
				foreach(explode(",", $result[4]) as $tag) {
					$sTag = explode(":", $tag);
					if(!is_numeric($sTag[0])) {
						continue;
					}
					switch($sTag[0]) {
						case "0":
							if(!is_string($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->setNamedTagEntry(new StringTag($sTag[1], $sTag[2]));
						break;
						case "1":
							if(!is_numeric($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->setNamedTagEntry(new IntTag($sTag[1], (int)$sTag[2]));
						break;
						default:
							$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
						break;
					}
				}
			}
			$sender->getInventory()->setItemInHand($item);
			$sender->sendMessage(TextFormat::GREEN  . "The item in your hand has some modifications.");
		});
		$nForm->setTitle("Editor");
		$nForm->addInput("CustomName", $item->getName(), $item->getName());
		$nForm->addInput("Lore", join("\n", $item->getLore()), join("\n", $item->getLore()));
		$nForm->addInput("Remove Enchantments", "ID Enchantment", "false");
		$nForm->addInput("Remove CompoundTag", "false", "false");
		$nForm->addInput("Add CompoundTag", "false", "false");
		$nForm->addLabel("* Plugin can only support StringTag, IntTag.");
		$nForm->addLabel("- 0:string:string is for StringTag");
		$nForm->addLabel("- 1:string:int for IntTag");
		$nForm->addLabel("* You can also put an array of CompundTag when you put a comma after each Tag.");
		$sender->sendForm($nForm);
	}
}
