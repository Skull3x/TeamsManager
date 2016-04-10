<?php

namespace Team\Teams;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\Level;
use pocketmine\entity\Effect;

class TeamMain extends PluginBase implements Listener{
    
    public $economyAPI = null;
    public $messages, $cf;
    
    private $TeamHistoryTracker;
	
	public function onEnable(){
        //$this->TeamHistoryTracker = new TeamHistoryTracker($this);
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."Players/");
        $this->getLogger()->info(TextFormat::DARK_AQUA . "has been successfully enabled");
        $this->getServer()->getPluginManager()->registerEvents($this ,$this);
        $this->config2 = new Config($this->getDataFolder() . "/Team.yml", Config::YAML);
        $this->cf = (new Config($this->getDataFolder() . "/TeamKillMoney.yml", Config::YAML, ["Amount" => 100]))->getAll();
        $this->config2->save();
        if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
            $this->economyAPI = \onebone\economyapi\EconomyAPI::getInstance();
        }else{
            $this->getLogger()->error(TextFormat::RED ."The plugin EconomyAPi by onebone is missing!");
			$this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }
    
    public function getTeamHistoryTracker(){
        return $this->TeamHistoryTracker;
    }
    
    public function onPickup(InventoryPickupItemEvent $event){
        $inv = $event->getInventory();
		if(!($inv instanceof PlayerInventory)){
			return;
        }
		$player = $inv->getHolder();
		if(!($player instanceof Player)){
			return;
		}
        $item = $event->getItem();
        $fizz = new AnvilFallSound($inv);
        $inv->getLevel()->addSound($fizz);
        $amount = $this->cf["MoneyAmount"];
        $this->economyAPI->addMoney($player, $amount);
        $this->PlayerFile->set("EXP",$amount);
        $this->PlayerFile->save();
    }
    
    public function onDrop(PlayerDropItemEvent $event){
            $player = $event->getPlayer();
            if($player->getInventory()->getItemInHand()->getId() === Item::EMERALD){
                $event->setCancelled(true);
            }
        }
    
    public function onCommand(CommandSender $player, Command $cmd, $label, array $args){
        switch($cmd->getName()){
            case "tm":
                if($player->hasPermission("tm.use")){
                    if(!empty($args[0])){
                        if($args[0]=="red"){
                            $team = "§4[ Red ]";
                        }else
                            if($args[0]=="blue"){
                                $team = "§9[ Blue ]";
                            }else
                                if($args[0]=="green"){
                                    $team = "§a[ Green ]";
                                }else
                                    if($args[0]=="yellow"){
                                        $team = "§e[ Yellow ]";
                                    }
                        $config = new Config($this->getDataFolder() . "/team.yml", Config::YAML);
                        $name = $player->getName();
                        $player->setNameTag($teamcolor . $name);
					    $config->set($name,$team);
					    $config->save();
                        $player->getInventory()->clearAll();
                        $player->getInventory()->addItem(Item::get(322, 0, 1));
                        $player->getInventory()->addItem(Item::get(438, 21, 5));
                        $player->getInventory()->setItemInHand(Item::get(ITEM::DIAMOND_SWORD), $player);
                        $player->getInventory()->setHelmet(Item::get(302, 0, 1));
		                $player->getInventory()->setChestplate(Item::get(303, 0, 1));
		                $player->getInventory()->setLeggings(Item::get(304, 0, 1));
		                $player->getInventory()->setBoots(Item::get(305, 0, 1));
		                $player->getInventory()->sendArmorContents($player);
                        $effect1 = Effect::getEffect (1);
		                $effect1->setDuration (9999);
		                $effect1->setAmplifier (2);
		                $effect2 = Effect::getEffect (5);
		                $effect2->setDuration (9999);
		                $effect2->setAmplifier (1);
		                $effect3 = Effect::getEffect (10);
		                $effect3->setDuration (9999);
		                $effect3->setAmplifier (5);
                        $player->addEffect($effect1);
                        $player->addEffect($effect2);
                        $player->addEffect($effect3);
					    $player->sendMessage(TextFormat::BLUE . TextFormat::BOLD ."»". TextFormat::WHITE ."You entered to the " . $team . TextFormat::WHITE ." team");
                    }else{
                        $player->sendMessage(TextFormat::AQUA ."Teams:");
                        $player->sendMessage(TextFormat::WHITE . TextFormat::BOLD ."»". TextFormat::RESET . TextFormat::RED ."Red");
                        $player->sendMessage(TextFormat::WHITE . TextFormat::BOLD ."»". TextFormat::RESET . TextFormat::BLUE ."Blue");
                        $player->sendmessage(TextFormat::WHITE . TextFormat::BOLD ."»". TextFormat::RESET . TextFormat::GREEN ."Green");
                        $player->sendMessage(TextFormat::WHITE . TextFormat::BOLD ."»". TextFormat::RESET . TextFormat::YELLOW ."Yellow");
                    }
                    return true;
                }
        }
    }
        
        /*public function onDeath(PlayerDeathEvent $event){
            $ign = $player->getName();
            $this->PlayerFile = new Config($this->getDataFolder()."Players/". $ign .".yml", Config::YAML);
            $event->setDrops(array(Item::EMERALD));
            $inv = $event->getPlayerInventory();
            $player = $event->getPlayer();
            $m = $this->PlayerFile->get("EXP");
            $a = $m - $amount;
            $this->PlayerFile->set("EXP", $a);
	        $this->PlayerFile->save();
            if($event instanceof InventoryPickupItemEvent){
                if($player->getInventory()->getItem()->getId() === Item::EMERALD){
                    $fizz = new AnvilFallSound($player);
                    $player->getLevel()->addSound($fizz);
                    $amount = $this->cf["MoneyAmount"];
                    $this->economyAPI->addMoney($player, $amount);
                    $this->PlayerFile->set("EXP",$amount);
                    $this->PlayerFile->save();
                }
            }
        }*/
    public function onDeath(PlayerDeathEvent $event){
        $ign = $event->getPlayer()->getName();
        $player = $event->getPlayer();
        $file = ($this->getDataFolder()."Players/".$ign.".yml");
        if(!file_exists($file)){
            $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign.".yml", Config::YAML);
            $this->PlayerFile->save();
            $fizz = new AnvilFallSound($player);
            $player->getLevel()->addSound($fizz);
        }
    }

public function onEntityDamage(EntityDamageEvent $event){
    if($event instanceof EntityDamageByChildEntityEvent){
    	$entity = $event->getDamager();
    	if($entity instanceof Player){
    		$projectile = $event->getChild();
                if($projectile instanceof Snowball){
                	$event->setCancelled(false);
                        $event->setKnockBack($event->getKnockBack() * 2);
                        if($event instanceof EntityDamageByEntityEvent){
                            if($event->getEntity() instanceof Player && $event->getDamager() instanceof Player){
                                $playern = $event->getEntity()->getNameTag();
                                $damagern = $event->getDamager()->getNameTag();
                                $damager = $event->getDamager();
                                $player = $event->getEntity();
                                if((strpos($playern, "§4[ RED ]") !== false) && (strpos($damagern, "§4[ RED ]") !== false)){
                                    $event->setCancelled();
                                }else if((strpos($playern, "§9[ BLUE ]") !== false) && (strpos($damagern, "§9[ BLUE ]") !== false)){
                                    $event->setCancelled();
                                }else if((strpos($playern, "§a[ GREEN ]") !== false) && (strpos($damagern, "§a[ GREEN ]") !== false)){
                                    $event->setCancelled();
                                }else if((strpos($playern, "§e[ YELLOW ]") !== false) && (strpos($damagern, "§e[ YELLOW ]") !== false)){
                                    $event->setCancelled();
                                }
                            }
                        }
                    }
                }
            }
    }
}
}