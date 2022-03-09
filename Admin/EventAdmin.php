<?php

namespace Pixel\EventBundle\Admin;


use Pixel\EventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class EventAdmin extends Admin
{
    const EVENT_LIST_VIEW = 'event.events_list';
    const EVENT_ADD_FORM_VIEW = 'event.event_add_form';
    const EVENT_ADD_DETAILS_FORM = 'event.event_add_form_details';
    const EVENT_EDIT_FORM_VIEW = 'event.event_edit_form';
    const EVENT_EDIT_DETAILS_FORM_VIEW = 'event.event_edit_form_detail';
    const EVENT_EDIT_SEO_FORM_VIEW = 'event.event_edit_form_seo';

    /**
     * @var ViewBuilderFactoryInterface
     */
    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;
    private WebspaceManagerInterface $webspaceManager;
    private ActivityViewBuilderFactoryInterface $activityViewBuilderFactory;

    public function __construct(
        ViewBuilderFactoryInterface         $viewBuilderFactory,
        SecurityCheckerInterface            $securityChecker,
        WebspaceManagerInterface            $webspaceManager,
        ActivityViewBuilderFactoryInterface $activityViewBuilderFactory
    )
    {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
        $this->webspaceManager = $webspaceManager;
        $this->activityViewBuilderFactory = $activityViewBuilderFactory;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $eventNavigationItem = new NavigationItem('events');
            $eventNavigationItem->setView(static::EVENT_LIST_VIEW);
            $eventNavigationItem->setIcon('su-calendar');
            $eventNavigationItem->setPosition(15);
            $navigationItemCollection->add($eventNavigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->webspaceManager->getAllLocales();
        $formToolbarActions = [];
        $listToolbarActions = [];
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
            $formToolbarActions[] = new TogglerToolbarAction('event.is_active',
                'enabled',
                'enable',
                'disable');
        }
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }
        if ($this->securityChecker->hasPermission(Event::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $listview = $this->viewBuilderFactory->createListViewBuilder(static::EVENT_LIST_VIEW, '/events/:locale')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setListKey(Event::LIST_KEY)
                ->addListAdapters(['table'])
                ->addLocales($locales)
                ->setDefaultLocale($locales[0])
                ->setAddView(static::EVENT_ADD_FORM_VIEW)
                ->setEditView(static::EVENT_EDIT_FORM_VIEW)
                ->addToolbarActions($listToolbarActions);
            $viewCollection->add($listview);

            $addFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(static::EVENT_ADD_FORM_VIEW, '/events/:locale/add')
                ->setResourceKey(Event::FORM_KEY)
                ->addLocales($locales)
                ->setBackView(static::EVENT_LIST_VIEW);
            $viewCollection->add($addFormView);

            $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::EVENT_ADD_DETAILS_FORM, '/details')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setFormKey(Event::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->setEditView(static::EVENT_EDIT_FORM_VIEW)
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EVENT_ADD_FORM_VIEW);
            $viewCollection->add($addDetailsFormView);

            $editFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(static::EVENT_EDIT_FORM_VIEW, '/events/:locale/:id')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->addLocales($locales)
                ->setBackView(static::EVENT_LIST_VIEW);
            $viewCollection->add($editFormView);

            $editDetailsFormView = $this->viewBuilderFactory->createPreviewFormViewBuilder(static::EVENT_EDIT_DETAILS_FORM_VIEW, '/details')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setFormKey(Event::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EVENT_EDIT_FORM_VIEW);
            $viewCollection->add($editDetailsFormView);

            $editSeoFormView = $this->viewBuilderFactory->createFormViewBuilder(static::EVENT_EDIT_SEO_FORM_VIEW, '/seo')
                ->setResourceKey(Event::RESOURCE_KEY)
                ->setFormKey(Event::SEO_FORM_KEY)
                ->setTabTitle('sulu_page.seo')
                ->addToolbarActions($formToolbarActions)
                ->setTitleVisible(true)
                ->setTabOrder(2048)
                ->setParent(static::EVENT_EDIT_FORM_VIEW);
            $viewCollection->add($editSeoFormView);

            if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
                $viewCollection->add(
                    $this->activityViewBuilderFactory->createActivityListViewBuilder(static::EVENT_EDIT_FORM_VIEW . ".activity", "/activity", Event::RESOURCE_KEY)
                        ->setParent(static::EVENT_EDIT_FORM_VIEW)
                );
            }
        }
    }

    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Event' => [
                    Event::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }
}