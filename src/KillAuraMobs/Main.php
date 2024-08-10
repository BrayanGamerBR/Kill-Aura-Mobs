<?php

namespace KillAuraMobs;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\item\VanillaItems;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
    /** @var bool[] */
    private $auraActive = [];
    /** @var TaskHandler[] */
    private $tasks = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("Modo hack KillAura activado!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "killaura" && $sender instanceof Player) {
            $playerName = $sender->getName();
            if (isset($this->auraActive[$playerName])) {
                unset($this->auraActive[$playerName]);
                $sender->sendMessage(TextFormat::RED . "Modo hack KillAura desactivado!.");
                if (isset($this->tasks[$playerName])) {
                    $this->tasks[$playerName]->cancel();
                    unset($this->tasks[$playerName]);
                }
            } else {
                $this->auraActive[$playerName] = true;
                $sender->sendMessage(TextFormat::GOLD . "El hack de KillAura para mobs esta activado!." . TextFormat::EOL . 
                                     TextFormat::AQUA . TextFormat::BOLD . "Sigueme en Tik Tok: " . TextFormat::RESET . TextFormat::AQUA . "@brayanag2000." . TextFormat::EOL . 
                                     TextFormat::GREEN . "Mas plugins, muy pronto!");
                $this->tasks[$playerName] = $this->activateKillAura($sender);
            }
            return true;
        }
        return false;
    }

    private function activateKillAura(Player $player): TaskHandler {
        return $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($player) {
            if (isset($this->auraActive[$player->getName()]) && $player->isOnline()) {
                foreach ($player->getWorld()->getEntities() as $entity) {
                    if ($entity instanceof Living && !$entity instanceof Player && $player->getPosition()->distance($entity->getPosition()) <= 5) {
                        $event = new EntityDamageByEntityEvent($player, $entity, EntityDamageByEntityEvent::CAUSE_MAGIC, 2);
                        if (!$event->isCancelled()) {
                            $entity->attack($event);
                            if ($player->getInventory()->getItemInHand()->equals(VanillaItems::STICK())) {
                                $this->launchEntity($entity, $player);
                            }
                        }
                    }
                }
            }
        }), 10); // Runs every half-second
    }

    private function launchEntity(Entity $entity, Player $player): void {
        $directionVector = $player->getDirectionVector()->multiply(2); // Adjust multiplier to control launch power
        $entity->setMotion(new Vector3($directionVector->x, 1.5, $directionVector->z));
    }
}

