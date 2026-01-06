<?php

namespace br\com\safereturn;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\math\Vector3;

class GraveManager {

    private $plugin;
    private $graves = [];
    private $config;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->config = new Config($plugin->getDataFolder() . "graves.yml", Config::YAML);
        $this->loadGraves();

        // Tarefa para gerenciar expiração e partículas
        $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $this->tick();
        }), 20); // Roda a cada 1 segundo
    }

    public function createGrave(Player $player, array $drops, Position $pos): void {
        if(empty($drops)) return;

        $id = uniqid();
        $expiry = time() + $this->plugin->getConfig()->get("settings.expire_time", 600);

        $graveData = [
            "uuid" => $player->getUniqueId()->toString(),
            "name" => $player->getName(),
            "x" => floor($pos->x),
            "y" => floor($pos->y),
            "z" => floor($pos->z),
            "world" => $pos->getWorld()->getFolderName(),
            "items" => array_map(function(Item $item){ return $item->nbtSerialize(); }, $drops),
            "expiry" => $expiry
        ];

        $this->graves[$id] = $graveData;
        
        // Coloca o bloco físico
        $blockName = $this->plugin->getConfig()->get("settings.grave_block", "chest");
        // Nota: Aqui simplificamos para Baú, para outros blocos precisa de parsing mais complexo
        $block = VanillaBlocks::CHEST(); 
        $pos->getWorld()->setBlock($pos, $block);

        $msg = str_replace(["{X}", "{Y}", "{Z}"], [$graveData['x'], $graveData['y'], $graveData['z']], $this->plugin->getConfig()->get("messages.grave_created"));
        $player->sendMessage($this->plugin->getConfig()->get("messages.prefix") . $msg);
    }

    public function claimGrave(Player $player, Position $pos): bool {
        foreach ($this->graves as $id => $data) {
            if ($data['world'] === $pos->getWorld()->getFolderName() &&
                $data['x'] == $pos->x && $data['y'] == $pos->y && $data['z'] == $pos->z) {

                if ($data['uuid'] !== $player->getUniqueId()->toString() && !$player->hasPermission("safereturn.admin")) {
                    $msg = str_replace("{PLAYER}", $data['name'], $this->plugin->getConfig()->get("messages.not_yours"));
                    $player->sendMessage($this->plugin->getConfig()->get("messages.prefix") . $msg);
                    return false; // Cancela a abertura do bloco
                }

                // Devolver itens
                $inventory = $player->getInventory();
                $items = [];
                foreach($data['items'] as $nbt){
                    $items[] = Item::nbtDeserialize($nbt);
                }
                
                $leftover = $inventory->addItem(...$items);
                
                // Dropa o que sobrou se inv estiver cheio
                foreach($leftover as $item){
                    $player->getWorld()->dropItem($player->getPosition(), $item);
                    $player->sendMessage($this->plugin->getConfig()->get("messages.prefix") . $this->plugin->getConfig()->get("messages.inventory_full"));
                }

                // Remove o túmulo
                $pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());
                unset($this->graves[$id]);
                
                $player->sendMessage($this->plugin->getConfig()->get("messages.prefix") . $this->plugin->getConfig()->get("messages.grave_claimed"));
                return true;
            }
        }
        return false;
    }

    public function isGraveBlock(Position $pos): bool {
        foreach ($this->graves as $data) {
            if ($data['world'] === $pos->getWorld()->getFolderName() &&
                $data['x'] == $pos->x && $data['y'] == $pos->y && $data['z'] == $pos->z) {
                return true;
            }
        }
        return false;
    }

    private function tick(): void {
        foreach ($this->graves as $id => $data) {
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($data['world']);
            if (!$world) continue;

            $pos = new Vector3($data['x'], $data['y'], $data['z']);

            // Verifica Expiração
            if (time() >= $data['expiry']) {
                $action = $this->plugin->getConfig()->get("settings.expire_action", "drop");
                
                if($action === "drop"){
                     foreach($data['items'] as $nbt){
                        $world->dropItem($pos->add(0.5, 1, 0.5), Item::nbtDeserialize($nbt));
                     }
                }
                
                $world->setBlock($pos, VanillaBlocks::AIR());
                unset($this->graves[$id]);
                continue;
            }

            // Partícula de Texto (Holograma Simples)
            $timeLeft = $data['expiry'] - time();
            $mins = floor($timeLeft / 60);
            $secs = $timeLeft % 60;
            $text = "§b† §3Túmulo de §f{$data['name']} §3§b†\n§7Expira em: §e{$mins}m {$secs}s";
            
            $world->addParticle($pos->add(0.5, 1.5, 0.5), new FloatingTextParticle("", $text));
        }
    }

    public function loadGraves(): void {
        $this->graves = $this->config->getAll();
    }

    public function saveGraves(): void {
        $this->config->setAll($this->graves);
        $this->config->save();
    }
}
