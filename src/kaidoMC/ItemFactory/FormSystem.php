<?php

/**
 *  _         _     _       __  __  ____ 
 * | | ____ _(_) __| | ___ |  \/  |/ ___|
 * | |/ / _` | |/ _` |/ _ \| |\/| | |    
 * |   < (_| | | (_| | (_) | |  | | |___ 
 * |_|\_\__,_|_|\__,_|\___/|_|  |_|\____|
 */

declare(strict_types=1);

namespace kaidoMC\ItemFactory;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use NhanAZ\libBedrock\StringToItem;
use pocketmine\player\Player;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class FormSystem {

	public function getForm(Player $sender): void {
		$nForm = new SimpleForm(function (Player $sender, ?int $result) {
			if ($result === null) {
				return;
			}
			switch ($result) {
				case 0:
					$this->getFormCreate($sender);
					break;
				case 1:
					$this->getFormClone($sender);
					break;
				case 2:
					$this->getFormItem($sender);
					break;
			}
		});
		$nForm->setTitle("Items");
		$nForm->addButton("CREATE");
		$nForm->addButton("CLONE");
		$nForm->addButton("EDIT");
		$sender->sendForm($nForm);
	}

	private function getFormCreate(Player $sender): void {
		$nForm = new CustomForm(function (Player $sender, ?array $result) {
			if ($result === null) {
				return;
			}
			$itemId = explode(":", $result[1]);
			if (!isset($itemId[0]) || !isset($itemId[1])) {
				$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
				return;
			}

			if (!is_numeric($itemId[0]) || !is_numeric($itemId[1])) {
				$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
				return;
			}

			if (!is_numeric($result[2]) || (int) $result[2] < 1) {
				$sender->sendMessage(TextFormat::RED . "Wrong format try again or the number of items in trouble.");
				return;
			}

			try {
				$item = StringToItem::parse($itemId[0] . ":" . $itemId[1]);
			} catch (\NhanAZ\libBedrock\libBedrockException $event) {
				$sender->sendMessage(TextFormat::RED . "Problem: " . $event->getMessage());
				return;
			}
			$item->setCount((int) $result[2]);

			if ($result[3] !== "false") {
				$item->setCustomName($result[3]);
			}
			if ($result[4] !== "false") {
				$item->setLore(explode(",", $result[4]));
			}

			if ($result[5] !== "false") {
				foreach (explode(",", $result[5]) as $sEnc) {
					$encId = explode(":", $sEnc);
					if (!is_numeric($encId[0]) or !is_numeric($encId[1])) {
						continue;
					}
					$type = EnchantmentIdMap::getInstance()->fromId((int) $encId[0]);
					if ($type !== null) {
						$item->addEnchantment(new EnchantmentInstance($type, (int)$encId[1]));
					}
				}
			}

			if ($result[6] !== "false") {
				foreach (explode(",", $result[6]) as $tag) {
					$sTag = explode(":", $tag);
					if (!is_numeric($sTag[0])) {
						continue;
					}
					switch ($sTag[0]) {
						case "0":
							if (!is_string($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->getNamedTag()->setString($sTag[1], $sTag[2]);
							break;
						case "1":
							if (!is_numeric($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->getNamedTag()->setInt($sTag[1], (int)$sTag[2]);
							break;
						default:
							$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
							break;
					}
				}
			}

			if ($result[10] !== "false") {
				$item->getNamedTag()->setString("KND", $result[10]);
			}

			if ($result[11] !== false) {
				$item->getNamedTag()->setString("canAction", "ToiYeuVietNam");
			}
			$sender->getInventory()->addItem($item);
		});
		$nForm->setTitle("Items");
		$nForm->addLabel("* Notes: Putting a comma after each feature is considered an array.");
		$nForm->addInput("ID & Meta (Example: 1:0)");
		$nForm->addInput("Count", "1", "1");
		$nForm->addInput("CustomName", "N", "false");
		$nForm->addInput("Lore", "N", "false");
		$nForm->addInput("ID Enchantment & Level (Example: 9:1)", "N", "false");
		$nForm->addInput("CompoundTag (Example: 0:n:n)", "N", "false");
		$nForm->addLabel("* Plugin can only support StringTag, IntTag.");
		$nForm->addLabel("- 0:string:string is for StringTag");
		$nForm->addLabel("- 1:string:int for IntTag");
		$nForm->addInput("Commands (Example: rca {player} jump)", "N", "false");
		$nForm->addToggle("Throw it on the ground", false);
		$sender->sendForm($nForm);
	}

	private function getFormClone(Player $sender): void {
		$item = $sender->getInventory()->getItemInHand();
		if ($item->equals(VanillaItems::AIR())) {
			$sender->sendMessage(TextFormat::RED . "Can't be done if you don't have the item on hand.");
			return;
		}

		if ($sender->getInventory()->canAddItem($item)) {
			$sender->getInventory()->addItem($item);
			$sender->sendMessage(TextFormat::GREEN  . "The item in your hand has been cloned and added to your inventory.");
		} else {
			$sender->sendMessage(TextFormat::RED . "Your inventory doesn't have enough space to add items.");
		}
	}

	private function getFormItem(Player $sender): void {
		$item = $sender->getInventory()->getItemInHand();
		if ($item->equals(VanillaItems::AIR())) {
			$sender->sendMessage(TextFormat::RED . "Can't be done if you don't have the item on hand.");
			return;
		}
		$nForm = new CustomForm(function (Player $sender, ?array $result) use ($item) {
			if ($result === null) {
				return;
			}
			if ($item->getName() !== $result[0]) {
				$item->setCustomName($result[0]);
			}
			if (join("\n", $item->getLore()) !== $result[1]) {
				$item->setLore(explode(",", $result[1]));
			}
			if (is_numeric($result[2])) {
				#if($item->hasEnchantment((int) $result[2])) {
				if ($item->hasEnchantment(EnchantmentIdMap::getInstance()->fromId((int)$result[2]))) {
					$item->removeEnchantment(EnchantmentIdMap::getInstance()->fromId((int)$result[2]));
				}
			}
			if ($result[3] !== "false") {
				if ($item->getNamedTag()->getTag($result[3]) !== null) {
					$item->getNamedTag()->removeTag($result[3]);
				}
			}
			if ($result[4] !== "false") {
				foreach (explode(",", $result[4]) as $tag) {
					$sTag = explode(":", $tag);
					if (!is_numeric($sTag[0])) {
						continue;
					}
					switch ($sTag[0]) {
						case "0":
							if (!is_string($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->getNamedTag()->setString($sTag[1], $sTag[2]);
							break;
						case "1":
							if (!is_numeric($sTag[2])) {
								$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
								return;
							}
							$item->getNamedTag()->setInt($sTag[1], (int)$sTag[2]);
							break;
						default:
							$sender->sendMessage(TextFormat::RED . "Wrong format try again.");
							break;
					}
				}
			}
			if ($result[8] !== "false") {
				$item->getNamedTag()->setString("KND", $result[8]);
			}
			$sender->getInventory()->setItemInHand($item);
			$sender->sendMessage(TextFormat::GREEN  . "The item in your hand has some modifications.");
		});
		$nForm->setTitle("Items");
		$nForm->addInput("CustomName", $item->getName(), $item->getName());
		$nForm->addInput("Lore", join("\n", $item->getLore()), join("\n", $item->getLore()));
		$nForm->addInput("Remove Enchantments", "ID Enchantment", "false");
		$nForm->addInput("Remove CompoundTag", "false", "false");
		$nForm->addInput("Add CompoundTag", "false", "false");
		$nForm->addLabel("* Plugin can only support StringTag, IntTag.");
		$nForm->addLabel("- 0:string:string is for StringTag");
		$nForm->addLabel("- 1:string:int for IntTag");
		$nForm->addInput("Set command", "N", "false");
		$sender->sendForm($nForm);
	}
}
