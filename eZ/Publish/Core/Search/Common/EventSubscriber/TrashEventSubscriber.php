<?php

namespace eZ\Publish\Core\Search\Common\EventSubscriber;

use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\Event\Trash\RecoverEvent;
use eZ\Publish\Core\Event\Trash\TrashEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrashEventSubscriber extends AbstractSearchEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            RecoverEvent::class => 'onRecover',
            TrashEvent::class => 'onTrash',
        ];
    }

    public function onRecover(RecoverEvent $event)
    {
        $this->indexSubtree($event->getLocation()->id);
    }

    public function onTrash(TrashEvent $event)
    {
        if ($event->getTrashItem() instanceof TrashItem) {
            $this->searchHandler->deleteContent(
                $event->getLocation()->contentId
            );
        }

        $this->searchHandler->deleteLocation(
            $event->getLocation()->id,
            $event->getLocation()->contentId
        );
    }
}