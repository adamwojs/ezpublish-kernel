<?php

namespace eZ\Publish\Core\Search\Common\EventSubscriber;

use eZ\Publish\Core\Event\Section\AssignSectionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SectionEventSubscriber extends AbstractSearchEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AssignSectionEvent::class => 'onAssignSection',
        ];
    }

    public function onAssignSection(AssignSectionEvent $event)
    {
        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($event->getContentInfo()->id);
        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load($contentInfo->id, $contentInfo->currentVersionNo)
        );
    }
}