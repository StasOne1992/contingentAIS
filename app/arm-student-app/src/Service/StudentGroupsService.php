<?php

namespace App\Service;

use App\Entity\AccessSystemControl;
use App\Entity\Gender;
use App\Entity\Student;
use App\Repository\StudentGroupsRepository;
use Npub\Gos\Snils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\DateTime;


class StudentGroupsService extends AbstractController
{
    protected $groupID;


    public function __construct(
        private StudentGroupsRepository $studentGroupsRepository,
        private XlsxService             $xlsxService,
        private GlobalHelpersService    $globalHelpersService,
        private RFIDService             $RFIDService,
    )
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public
    function countWoman($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            /**
             * @var Student $student
             * @var Gender $current
             */
            $current = $student->getGender();
            if ($current->getId() == 2) $count = $count + 1;
        }
        return $count;
    }

    public
    function countMan($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            /**
             * @var Student $student
             * @var Gender $current
             */
            $current = $student->getGender();
            if ($current->getId() == 1) $count = $count + 1;
        }
        return $count;
    }

    public
    function countOrphan($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            /**
             * @var Student $student
             * @var bool $current
             */
            $current = $student->isIsOrphan() ?? false;
            if ($current) {
                $count = $count + 1;}
        }
        return $count;
    }

    public
    function countInvalid($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            /**
             * @var Student $student
             * @var bool $current
             */
            $current = $student->isIsInvalid() ?? false;
            if ($current == true) $count = $count + 1;
        }
        return $count;
    }

    public
    function countUnderage($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            /**
             * @var Student $student
             * @var DateTime $current
             */
            $current = $student->getBirthData();

            $now = date_create('now');
            $current = $now->diff($current)->y;

            $current = (int)$current;

            if ($current < 18) $count = $count + 1;
        }
        return $count;
    }

    public
    function countАdult($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->getBirthData();
            /**
             * @var Student $student
             * @var DateTime $current
             */
            $now = date_create('now');
            $current = $now->diff($current)->y;

            $current = (int)$current;

            if ($current >= 18) $count = $count + 1;
        }
        return $count;
    }

    public
    function countWithoutParents($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            /**
             * @var Student $student
             * @var bool $current
             */
            $current = $student->isIsWithoutParents() ?? false;
            if ($current == true) $count = $count + 1;

        }
        return $count;
    }

    public
    function countPoor($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            /**
             * @var Student $student
             * @var bool $current
             */
            $current = $student->isIsPoor() ?? false;
            if ($current == true) $count = $count + 1;

        }
        return $count;
    }

    public
    function generateSocialPasport($groupObj): array
    {
        $socialPassport = [];
        /**
         * coutnStudents - всего
         * countWoman - женщины
         * countMan - мужчины
         * countOrphan - сироты
         * countInvalid - инвалиды и ОВЗ
         * countIsPoor - малоимущие
         * countUnderage - не совершеннолетние
         * countAdult - совершеннолетние
         * countWithoutParents - дети, оставшиеся без попечения родителей
         */
        $socialPassport['countStudents'] = count($groupObj->getActiveStudents()->getValues());
        $socialPassport['countWoman'] = $this->countWoman($groupObj);
        $socialPassport['countMan'] = $this->countMan($groupObj);
        $socialPassport['countOrphan'] = $this->countOrphan($groupObj);
        $socialPassport['countInvalid'] = $this->countInvalid($groupObj);
        $socialPassport['countWithoutParents'] = $this->countWithoutParents($groupObj);
        $socialPassport['countIsPoor'] = $this->countPoor($groupObj);
        $socialPassport['countUnderage'] = $this->countUnderage($groupObj);
        $socialPassport['countAdult'] = $this->countАdult($groupObj);

        return $socialPassport;
    }


    public function generateElearningListToImport($groupId)
    {
        $group = $this->studentGroupsRepository->find($groupId);
        $studentslist = $group->getActiveStudents()->toArray();
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $itter = 0;
        $data[$itter]['FirstName'] = 'Имя';
        $data[$itter]['MiddleName'] = 'Отчество';
        $data[$itter]['LastName'] = 'Фамилия';
        $data[$itter]['Email'] = 'email';
        $data[$itter]['Password'] = 'Пароль';
        $data[$itter]['Counry'] = 'Страна';
        $data[$itter]['Type'] = 'Тип';
        foreach ($studentslist as $student) {
            $itter = $itter + 1;
            $data[$itter]['FirstName'] = $student->getFirstName();
            $data[$itter]['MiddleName'] = $student->getMiddleName();
            $data[$itter]['LastName'] = $student->getLastName();
            $data[$itter]['Email'] = str_replace(' ', '', $student->getEmail());
            $data[$itter]['Password'] = $this->globalHelpersService->gen_password();
            $data[$itter]['Counry'] = 'RU';
            $data[$itter]['Type'] = 'ST';
        }
        return $this->xlsxService->generate($data, 'ЦКП-' . $group->getCode() . '.xlsx');
    }

    public function generateSchoolPortalList($groupId)
    {
        $group = $this->studentGroupsRepository->find($groupId);
        $studentslist = $group->getActiveStudents()->toArray();
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $itter = 0;
        $data[$itter]['FirstName'] = 'Имя';
        $data[$itter]['MiddleName'] = 'Отчество';
        $data[$itter]['LastName'] = 'Фамилия';
        $data[$itter]['Gender'] = 'Пол';
        $data[$itter]['BirthDate'] = 'Дата рождения';
        $data[$itter]['DateInPoo'] = 'Дата прибытия учащегося в ПОО';
        $data[$itter]['DateInGroup'] = 'Дата прибытия учащегося в группу';
        $data[$itter]['SNILS'] = 'СНИЛС учащегося';
        $data[$itter]['SORSER'] = 'Свидетельство рождения учащегося - серия';
        $data[$itter]['SORNUM'] = 'Свидетельство рождения учащегося - номер';
        $data[$itter]['PassportSeries'] = 'Паспорт РФ учащегося - серия';
        $data[$itter]['PassportNumber'] = 'Паспорт РФ учащегося - номер';
        foreach ($studentslist as $student) {
            $itter = $itter + 1;
            $data[$itter]['FirstName'] = $student->getFirstName();
            $data[$itter]['MiddleName'] = $student->getMiddleName();
            $data[$itter]['LastName'] = $student->getLastName();
            $data[$itter]['Gender'] = $student->getGender();
            $data[$itter]['BirthDate'] = $student->getBirthData()->format('d.m.Y');
            $data[$itter]['DateInPoo'] = $group->getDateStart()->format('d.m.Y');
            $data[$itter]['DateInGroup'] = $group->getDateStart()->format('d.m.Y');
            $snils = new Snils(substr($student->getDocumentSnils(), 0, 9));
            $data[$itter]['SNILS'] = $snils;
            $data[$itter]['SORSER'] = '';
            $data[$itter]['SORNUM'] = '';
            $data[$itter]['PassportSeries'] = $student->getPasportSeries();
            $data[$itter]['PassportNumber'] = $student->getPasportNumber();
        }
        return $this->xlsxService->generate($data, 'ШП-' . $group->getCode() . '.xlsx');
    }

    public function generatePerCo($groupId)
    {
        $group = $this->studentGroupsRepository->find($groupId);
        $studentslist = $group->getActiveStudents()->toArray();
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $itter = 0;
        $data[$itter]['itter'] = '№ п/п';
        $data[$itter]['UUID'] = 'Таб. №';
        $data[$itter]['LastName'] = 'Фамилия';
        $data[$itter]['FirstName'] = 'Имя';
        $data[$itter]['MiddleName'] = 'Отчество';
        $data[$itter]['Position'] = 'Должность';
        $data[$itter]['Group'] = 'Подразделение';
        $data[$itter]['DateInPoo'] = 'Дата прибытия учащегося в ПОО';
        $data[$itter]['CardSeries'] = 'Карта - серия';
        $data[$itter]['CardNumber'] = 'Карта - номер';
        $data[$itter]['StudentAccommodation'] = 'Проживает в общежитии';
        $data[$itter]['Gender'] = 'Пол';
        foreach ($studentslist as $student) {
            $itter = $itter + 1;
            $data[$itter]['itter'] = $itter;
            $data[$itter]['UUID'] = $student->getUUID();
            $data[$itter]['FirstName'] = $student->getFirstName();
            $data[$itter]['MiddleName'] = $student->getMiddleName();
            $data[$itter]['LastName'] = $student->getLastName();
            $data[$itter]['Position'] = 'Студент';
            $data[$itter]['Group'] = $group->getCode();
            $data[$itter]['DateInPoo'] = $group->getDateStart()->format('d.m.Y');;
            /**
             * @var AccessSystemControl $card
             */
            foreach ($student->getAccessSystemControls() as $card) {
                $data[$itter]['CardSeries'] = $card->getAccessCardSeries();
                $data[$itter]['CardNumber'] = $card->getAccesCardNumber();
            }
            if ($student->isIsLiveStudentAccommondation() == true) {
                $data[$itter]['StudentAccommodation'] = 'ture';
            } else {
                $data[$itter]['StudentAccommodation'] = '';
            }
            $data[$itter]['Gender'] = $student->getGender();
        }


        return $this->xlsxService->generate($data, 'PERCO-' . $group->getCode() . '.xlsx');
    }

    public function generateEnt($groupId)
    {
        $group = $this->studentGroupsRepository->find($groupId);
        $studentslist = $group->getActiveStudents()->toArray();
        $itter = 0;
        $data[$itter]['UUID'] = 'Таб. №';
        $data[$itter]['LastName'] = 'Фамилия';
        $data[$itter]['FirstName'] = 'Имя';
        $data[$itter]['MiddleName'] = 'Отчество';
        $data[$itter]['Position'] = 'Должность';
        $data[$itter]['Group'] = 'Подразделение';
        $data[$itter]['DateInPoo'] = 'Дата начала действия';
        $data[$itter]['CardID'] = 'Карта - ID';
        $data[$itter]['AccessRules'] = 'Схема доступа';
        foreach ($studentslist as $student) {
            $itter = $itter + 1;
            $data[$itter]['UUID'] = $student->getUUID();
            $data[$itter]['LastName'] = $student->getLastName();
            $data[$itter]['FirstName'] = $student->getFirstName();
            $data[$itter]['MiddleName'] = $student->getMiddleName();
            $data[$itter]['Position'] = 'Студент';
            $data[$itter]['Group'] = $group->getCode();
            $data[$itter]['DateInPoo'] = $group->getDateStart()->format('d.m.Y');;
            /**
             * @var AccessSystemControl $card
             */
            foreach ($student->getAccessSystemControls() as $card) {
                $convert = $this->RFIDService->convert('hid', str_pad($card->getAccessCardSeries(), 3, '0', STR_PAD_LEFT) . ',' . str_pad($card->getAccesCardNumber(), 6, '0', STR_PAD_LEFT));
                $data[$itter]['CardID'] = str_pad($card->getAccessCardSeries(), 3, '0', STR_PAD_LEFT) . '/' . str_pad($card->getAccesCardNumber(), 5, '0', STR_PAD_LEFT); //$convert['id'];
            }
            $data[$itter]['AccessRules'] = 'калитка_учебный_корпус';
        }
        return $this->xlsxService->generate($data, 'ЭНТ-' . $group->getCode() . '.xlsx');
    }
}