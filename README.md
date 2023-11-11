<h1>GiftPlayer<img src="asset/images.png" height="64" width="64" align="left"></img></h1><br/>


[![Lint](https://poggit.pmmp.io/ci.shield/MyFreds/GiftPlayer/GiftPlayer)](https://poggit.pmmp.io/ci/MyFreds/GiftPlayer/GiftPlayer)
[![Discord](https://img.shields.io/discord/979551565415346297.svg?label=&logo=discord&logoColor=ffffff&color=7389D8&labelColor=6A7EC2)](https://discord.gg/pKA9njAwyX)

<b>✨ The function of the PocketMine-MP plugin is to provide prizes in the form of items or blocks that are held to the target player</b>

# Config

```yaml
messages:
  player_not_found: "§l§eGiftPlayer: §r§cPlayer not found: §6{player}"
  
  not_enough_item: "§l§eGiftPlayer: §r§cYou don't have enough of that item in your hand."
  
  blocked_by_player: "§l§eGiftPlayer: §r§cYou are blocked by §6{player}. §cGift canceled."
  
  gave_gift: "§l§eGiftPlayer: §r§fYou gave §6{player} §e{amount} {itemName}"
  
  received_gift: "§l§eGiftPlayer: §r§fYou received a gift from §6{sender}: §e{amount} {itemName} §7» §b{message}"
  
  blocked_player: "§l§eGiftPlayer: §r§fYou have blocked §6{blockedPlayerName}."
  
  unblocked_player: "§l§eGiftPlayer: §r§fYou have unblocked §6{unblockedPlayerName}."
  
  not_blocked_player: "§l§eGiftPlayer: §r§c{unblockedPlayerName} is not blocked."
  
  blocked_players_list: "§l§eGiftPlayer: §r§fYou have blocked the following players: §6{blockedPlayers}"
  
  no_blocked_players: "§l§eGiftPlayer: §r§fYou have not blocked any players."
  
  gifthelp: "§7---------------- §l§6GIFTPLAYER §r§6COMMANDS §7----------------\n§e/gift §f<player> <amount> hand <message> §7# Send a gift to a player.\n§e/giftblock §f<player> §7# Block a player from sending you gifts.\n§e/giftunblock §f<player> §7# Unblock a player to receive gifts from them.\n§e/giftblocklist §7# View the list of blocked players.\n§e/gifthelp §7# Display this help message."
```

# TODO
- [x] Added the ability to edit all messages via configuration.
- [ ] ```/giftreload``` # Added command to reload GiftPlayer configuration 
- [ ] ```/giftall <player>``` # Gift All item in your inventory to target player
