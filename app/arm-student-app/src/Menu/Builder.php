<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class Builder
{
    private $factory;

    /**
     * Add any other dependency you n eed...
     */
    public function __construct(FactoryInterface $factory, private AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->factory = $factory;
    }

    public function mainMenu(array $options): ItemInterface
    {

        $menu = $this->factory->createItem('root');
        $menu->addChild('Home', ['uri' => '/dashboard']);
        if ($this->authorizationChecker->isGranted('ROLE_STAFF_AB_PETITIONS_R')) {
            $menu->addChild('Приемная комиссия', ['attributes' => ['class' => 'nav-main-heading']]);
            $menu->addChild('Панель управления',['route'=>'app_admission_dashboard_index']);
            $priem = $menu->addChild('Абитуриенты', ['uri' => '/', 'attributes' => ['dropdown' => true]]);
            $priem->addChild('Заявления', ['route' => 'app_abiturient_petition_index']);
            if ($this->authorizationChecker->isGranted('ROLE_STAFF_AB_PETITIONS_FULL')) {
                $admission = $menu->addChild('Приемная кампания', ['uri' => '/', 'attributes' => ['dropdown' => true]]);
                $admission->addChild('Приемные кампании', ['route' => 'app_admission_index']);
                $admission->addChild('КЦП', ['route' => 'app_admission_plan_index']);
            }
            $admission_reports=$menu->addChild('Отчёты по ПК', ['uri' => '/', 'attributes' => ['dropdown' => true]]);
            $admission_reports->addChild('Выполнение плана', ['route' => 'app_abiturient_petition_index']);
        }
        if ($this->authorizationChecker->isGranted('ROLE_STAFF_STUDENT_R')) {
            $menu->addChild('Учебная часть', ['attributes' => ['class' => 'nav-main-heading']]);
            $student = $menu->addChild('Студенты', ['uri' => '/', 'attributes' => ['dropdown' => true]]);
            $student->addChild('Список студентов', ['route' => 'app_student_index']);
            if ($this->authorizationChecker->isGranted('ROLE_STAFF_STUDENT_C')) {
                $student->addChild('Добавить студента', ['route' => 'app_student_new']);
            }
            $decree = $menu->addChild('Приказы', ['uri' => '#', 'attributes' => ['dropdown' => true]]);
            $decree->addChild('Приказы о движении', ['route' => 'app_contingent_document_index']);
            $menu->addChild('Студенчекие группы', ['route' => 'app_student_groups_index']);
        }
        if ($this->authorizationChecker->isGranted('ROLE_ROOT')) {
            $menu->addChild('Администрирование', ['attributes' => ['class' => 'nav-main-heading']]);
            $users = $menu->addChild('Пользователи', ['route' => 'app_user_index', 'attributes' => ['dropdown' => true]]);
            $users->addChild('Список пользователей', ['route' => 'app_user_index']);
            $users->addchild('Добавить', ['route' => 'app_user_new']);
            $users->addchild('Дейсвия пользователей', ['uri' => '#']);
            $users->addchild('Роли пользователей', ['uri' => '#']);
            $users = $menu->addChild('Справочники', ['attributes' => ['dropdown' => true]]);
            $users->addChild('Справочник. Виды законных представителей', ['route' => 'app_legal_representative_type_list_index']);
            $users->addchild('Справочник. Виды семей', ['route' => 'app_family_type_list_index']);
            $users->addchild('Дейсвия пользователей', ['uri' => '#']);
            $users->addchild('Роли пользователей', ['uri' => '#']);
            $users = $menu->addChild('Сотрудники', ['route' => 'app_staff_index', 'attributes' => ['dropdown' => true]]);
            $users->addChild('Список сотрудников', ['route' => 'app_staff_index']);
            $users->addchild('Добавить', ['route' => 'app_staff_new']);
            $users->addchild('Дейсвия пользователей', ['uri' => '#']);
            $users->addchild('Роли пользователей', ['uri' => '#']);
        }
        if ($this->authorizationChecker->isGranted('ROLE_STUDENT'))
        {
            $menu->addChild('Перейти в профиль студента', ['route' => 'app_student_dashboard','attributes'=>['class'=>'mt-6  ']]);
        }
        return $menu;
    }


    public function studentAppMainMenu(FactoryInterface $factory, array $options): ItemInterface
    {
        $menu = $factory->createItem('root');
        return $menu;
    }

}