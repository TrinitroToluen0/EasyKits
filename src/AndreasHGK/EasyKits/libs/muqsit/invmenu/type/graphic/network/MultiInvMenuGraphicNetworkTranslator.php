<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\libs\muqsit\invmenu\type\graphic\network;

use AndreasHGK\EasyKits\libs\muqsit\invmenu\session\InvMenuInfo;
use AndreasHGK\EasyKits\libs\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class MultiInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	/**
	 * @param InvMenuGraphicNetworkTranslator[] $translators
	 */
	public function __construct(
		private array $translators
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		foreach($this->translators as $translator){
			$translator->translate($session, $current, $packet);
		}
	}
}