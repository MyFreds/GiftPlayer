<?php

namespace Fred\GiftPlayer;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    public function onEnable(): void {
    $this->getLogger()->info("GiftPlayer plugin has been enabled.");
    $blockListDir = $this->getDataFolder() . "players/";
    if (!is_dir($blockListDir)) {
        @mkdir($blockListDir, 0777, true);
    }
}

    public function onDisable(): void {
        $this->getLogger()->info("GiftPlayer plugin has been disabled.");
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        if ($cmd->getName() === "gift") {
            if ($sender instanceof Player) {
                if (count($args) < 4) {
                    $sender->sendMessage("§eUsage: /gift <player> <amount> hand <message>");
                    return false;
                }

                $targetPlayer = $this->getServer()->getPlayerByPrefix($args[0]);

                if ($targetPlayer instanceof Player) {
                    $amount = (int)$args[1];

                    if ($amount <= 0) {
                        $sender->sendMessage("§cPlease specify a valid amount.");
                        return false;
                    }

                    $itemInHand = $sender->getInventory()->getItemInHand();
                    $message = implode(" ", array_slice($args, 3));

                    if (strtolower($args[2]) === "hand") {
                        $itemInHand = $sender->getInventory()->getItemInHand();

                        if ($itemInHand->getCount() < $amount) {
                            $sender->sendMessage("§l§eGiftPlayer: §r§cYou don't have enough of that item in your hand.");
                            return false;
                        }

                        // Check if the target player is in the block list
                        $blockListFile = $this->getDataFolder() . "players/" . $targetPlayer->getName() . "-blocklist.yml";
                        $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                        if (isset($blockList[$sender->getName()])) {
                            $sender->sendMessage("§l§eGiftPlayer: §r§cYou are blocked by §6{$targetPlayer->getName()}. §cGift canceled.");
                            return false;
                        }

                        $item = clone $itemInHand;
                        $item->setCount($amount);
                        $targetPlayer->getInventory()->addItem($item);

                        // Deduct the item from the sender's inventory
                        $itemInHand->setCount($itemInHand->getCount() - $amount);
                        $sender->getInventory()->setItemInHand($itemInHand);

                        $sender->sendMessage("§l§eGiftPlayer: §r§fYou gave §6{$targetPlayer->getName()} §e{$amount} " . $itemInHand->getName());
                        $targetPlayer->sendMessage("§l§eGiftPlayer: §r§fYou received a gift from §6{$sender->getName()}: §e{$amount} " . $itemInHand->getName() . " §7» §b{$message}");
                    } else {
                        $sender->sendMessage("§eUsage: /gift <player> <amount> hand <message>");
                        return false;
                    }

                    return true;
                } else {
                    $sender->sendMessage("§l§eGiftPlayer: §r§cPlayer not found: §6" . $args[0]);
                    return false;
                }
            } else {
                $sender->sendMessage("This command can only be run by a player in-game.");
                return false;
            }
        } elseif ($cmd->getName() === "giftblock") {
            if ($sender instanceof Player) {
                if (count($args) !== 1) {
                    $sender->sendMessage("§eUsage: /giftblock <player>");
                    return false;
                }

                $blockedPlayerName = $args[0];
                $blockListFile = $this->getDataFolder() . "players/" . $sender->getName() . "-blocklist.yml";
                
                $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                $blockList[$blockedPlayerName] = true;

                yaml_emit_file($blockListFile, $blockList);

                $sender->sendMessage("§l§eGiftPlayer: §r§fYou have blocked §6{$blockedPlayerName}.");
                return true;
            } else {
                $sender->sendMessage("This command can only be run by a player in-game.");
                return false;
            }
        } elseif ($cmd->getName() === "giftunblock") {
            if ($sender instanceof Player) {
                if (count($args) !== 1) {
                    $sender->sendMessage("§eUsage: /giftunblock <player>");
                    return false;
                }

                $unblockedPlayerName = $args[0];
                $blockListFile = $this->getDataFolder() . "players/" . $sender->getName() . "-blocklist.yml";

                // Load existing block list or create a new one
                $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                if (isset($blockList[$unblockedPlayerName])) {

                    unset($blockList[$unblockedPlayerName]);
                    
                    yaml_emit_file($blockListFile, $blockList);

                    $sender->sendMessage("§l§eGiftPlayer: §r§fYou have unblocked §6{$unblockedPlayerName}.");
                } else {
                    $sender->sendMessage("§l§eGiftPlayer: §r§c{$unblockedPlayerName} is not blocked.");
                }

                return true;
            } else {
                $sender->sendMessage("This command can only be run by a player in-game.");
                return false;
            }
        } elseif ($cmd->getName() === "giftblocklist") {
            if ($sender instanceof Player) {
                $blockListFile = $this->getDataFolder() . "players/" . $sender->getName() . "-blocklist.yml";

                // Load existing block list or create a new one
                $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                if (!empty($blockList)) {
                    $blockedPlayers = implode(", ", array_keys($blockList));
                    $sender->sendMessage("§l§eGiftPlayer: §r§fYou have blocked the following players: §6{$blockedPlayers}");
                } else {
                    $sender->sendMessage("§l§eGiftPlayer: §r§fYou have not blocked any players.");
                }

                return true;
            } else {
                $sender->sendMessage("This command can only be run by a player in-game.");
                return false;
            }
        } elseif ($cmd->getName() === "gifthelp") {
            $sender->sendMessage("§7---------------- §l§6GIFTPLAYER §r§6COMMANDS §7----------------");
            $sender->sendMessage("§e/gift §f<player> <amount> hand <message> §7# Send a gift to a player.");
            $sender->sendMessage("§e/giftblock §f<player> §7# Block a player from sending you gifts.");
            $sender->sendMessage("§e/giftunblock §f<player> §7# Unblock a player to receive gifts from them.");
            $sender->sendMessage("§e/giftblocklist §7# View the list of blocked players.");
            $sender->sendMessage("§e/gifthelp §7# Display this help message.");
            return true;
        }

        return true;
    }
}
