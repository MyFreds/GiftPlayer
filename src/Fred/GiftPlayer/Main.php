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
        
        $this->saveDefaultConfig();

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
                    $sender->sendMessage("§cUsage: /gift <player> <amount> hand <message>");
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
                        if ($itemInHand->getCount() < $amount) {
                            $sender->sendMessage($this->getConfig()->get("messages")["not_enough_item"]);
                            return false;
                        }

                        $blockListFile = $this->getDataFolder() . "players/" . $targetPlayer->getName() . "-blocklist.yml";
                        $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                        if (isset($blockList[$sender->getName()])) {
                            $blockedMessage = $this->getConfig()->get("messages")["blocked_by_player"];
                            $blockedMessage = str_replace("{player}", $targetPlayer->getName(), $blockedMessage);
                            $sender->sendMessage($blockedMessage);
                            return false;
                        }

                        $item = clone $itemInHand;
                        $item->setCount($amount);
                        $targetPlayer->getInventory()->addItem($item);

                        $itemInHand->setCount($itemInHand->getCount() - $amount);
                        $sender->getInventory()->setItemInHand($itemInHand);

                        $gaveGiftMessage = $this->getConfig()->get("messages")["gave_gift"];
                        $gaveGiftMessage = str_replace("{player}", $targetPlayer->getName(), $gaveGiftMessage);
                        $gaveGiftMessage = str_replace("{amount}", $amount, $gaveGiftMessage);
                        $gaveGiftMessage = str_replace("{itemName}", $itemInHand->getName(), $gaveGiftMessage);

                        $receivedGiftMessage = $this->getConfig()->get("messages")["received_gift"];
                        $receivedGiftMessage = str_replace("{sender}", $sender->getName(), $receivedGiftMessage);
                        $receivedGiftMessage = str_replace("{amount}", $amount, $receivedGiftMessage);
                        $receivedGiftMessage = str_replace("{itemName}", $itemInHand->getName(), $receivedGiftMessage);
                        $receivedGiftMessage = str_replace("{message}", $message, $receivedGiftMessage);

                        $sender->sendMessage($gaveGiftMessage);
                        $targetPlayer->sendMessage($receivedGiftMessage);
                    } else {
                        $sender->sendMessage($this->getConfig()->get("messages")["usage"]);
                        return false;
                    }

                    return true;
                } else {
                    $sender->sendMessage(str_replace("{player}", $args[0], $this->getConfig()->get("messages")["player_not_found"]));
                    return false;
                }
            } else {
                $sender->sendMessage("This command can only be run by a player in-game.");
                return false;
            }
        } elseif ($cmd->getName() === "giftblock") {
            if ($sender instanceof Player) {
                if (count($args) !== 1) {
                    $sender->sendMessage("§cUsage: /giftblock <player>");
                    return false;
                }

                $blockedPlayerName = $args[0];
                $blockListFile = $this->getDataFolder() . "players/" . $sender->getName() . "-blocklist.yml";
                
                $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                $blockList[$blockedPlayerName] = true;

                yaml_emit_file($blockListFile, $blockList);

                $sender->sendMessage(str_replace("{blockedPlayerName}", $blockedPlayerName, $this->getConfig()->get("messages")["blocked_player"]));
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

                $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                if (isset($blockList[$unblockedPlayerName])) {
                    unset($blockList[$unblockedPlayerName]);
                    yaml_emit_file($blockListFile, $blockList);
                    $sender->sendMessage(str_replace("{unblockedPlayerName}", $unblockedPlayerName, $this->getConfig()->get("messages")["unblocked_player"]));
                } else {
                    $sender->sendMessage(str_replace("{unblockedPlayerName}", $unblockedPlayerName, $this->getConfig()->get("messages")["not_blocked_player"]));
                }

                return true;
            } else {
                $sender->sendMessage("This command can only be run by a player in-game.");
                return false;
            }
        } elseif ($cmd->getName() === "giftblocklist") {
            if ($sender instanceof Player) {
                $blockListFile = $this->getDataFolder() . "players/" . $sender->getName() . "-blocklist.yml";

                $blockList = file_exists($blockListFile) ? yaml_parse_file($blockListFile) : [];

                $blockedPlayersListMessage = $this->getConfig()->get("messages")["blocked_players_list"];
                $noBlockedPlayersMessage = $this->getConfig()->get("messages")["no_blocked_players"];

                if (!empty($blockList)) {
                        $blockedPlayers = implode(", ", array_keys($blockList));
                        $sender->sendMessage(str_replace("{blockedPlayers}", $blockedPlayers, $blockedPlayersListMessage));
                    } else {
                        $sender->sendMessage($noBlockedPlayersMessage);
                    }

                    return true;
                } else {
                    $sender->sendMessage("This command can only be run by a player in-game.");
                    return false;
                }
            } elseif ($cmd->getName() === "gifthelp") {
                $sender->sendMessage($this->getConfig()->get("messages")["gifthelp"]);
                return true;
            }
           return true;
        }
    }
