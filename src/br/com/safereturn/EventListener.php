<?php

namespace br\com\safereturn;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\Player;

class EventListener implements Listener {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        
        // Salva local para o /backdeath
        $this->plugin->playerData->setNested($player->getName() . ".last_death", [
            "x" => $player->getPosition()->x,
            "y" => $player->getPosition()->y,
            "z" => $player->getPosition()->z,
            "world" => $player->getWorld()->getFolderName()
        ]);

        if(!$this->plugin->getConfig()->get("settings.enable")) return;

        // Verificações de Mundo
        if(in_array($player->getWorld()->getFolderName(), $this->plugin->getConfig()->get("settings.disabled_worlds", []))) return;

        // Verificação de PvP
        $cause = $player->getLastDamageCause();
        if($this->plugin->getConfig()->get("settings.disable_in_pvp") && $cause instanceof EntityDamageByEntityEvent && $cause->getDamager() instanceof Player){
            return; // Morreu em PvP, dropa normal
        }

        $drops = $event->getDrops();
        if(empty($drops)) return;

        // Cria o túmulo e remove drops do chão
        $this->plugin->getGraveManager()->createGrave($player, $drops, $player->getPosition());
        $event->setDrops([]); 
    }

    public function onInteract(PlayerInteractEvent $event): void {
        if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
        
        $block = $event->getBlock();
        if($this->plugin->getGraveManager()->isGraveBlock($block->getPosition())){
            $event->cancel(); // Cancela abrir o baú normal
            $this->plugin->getGraveManager()->claimGrave($event->getPlayer(), $block->getPosition());
        }
    }

    public function onBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        if($this->plugin->getGraveManager()->isGraveBlock($block->getPosition())){
            if(!$event->getPlayer()->hasPermission("safereturn.admin")){
                $event->cancel();
                $event->getPlayer()->sendMessage("§cVocê não pode quebrar este túmulo!");
            }
        }
    }
}
