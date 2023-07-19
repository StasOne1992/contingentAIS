<?php

namespace App\Service;

use App\Repository\ContingentDocumentRepository;
use App\Repository\CollegeRepository;
use App\Repository\StudentGroupsRepository;

class typicalDocuments
{
    private $college;
    private $collegeRepo;

    public function __construct(CollegeRepository $collegeRepository, StudentGroupsRepository $StudentGroupRepo)
    {
        $this->collegeRepo = $collegeRepository;
        $this->StudentGroupRepo = $StudentGroupRepo;
    }

    public function newpage($content)
    {
        return '<div class="main-page"><div class="sub-page">' . $content . '</div></div>';
    }

    private function setCollege($collegeId): void
    {
        $this->college = $this->collegeRepo->findBy(['id' => $collegeId])[0];
    }

    public function generateOrder($content): ?string
    {
        $this->setCollege(1);
        dump($this, $content);
        $header = str_replace("\r\n", '<br>', $this->college->getFullName());

        $headerAdd = '<p style="text-align: center;font-size: small">' . $this->college->getPostalAddress() . '<br>' . 'Телефон:' . $this->college->getPhone() . ' Факс:' . $this->college->getFax() . '<br>' . ' e-mail:' . $this->college->getEmail() . ' ' . $this->college->getWebSite() . '</p>';
        $header = '<img style="display: block; height:2cm; margin: 0 auto;" src="' . $this->college->getLogo() . '"><p style="text-align: center"><b>' . $header . '</b></p>' . $headerAdd;
        $subheader = '<h3 style="text-align: center;text-transform: uppercase">Приказ</h3>';
        $createDate = $content->getCreateDate();
        $createDate = $createDate->format('d.m.Y');
        $documentNumber = $content->getNumber();
        $subheader = $subheader . '<p style="text-align-last: justify">от' . $createDate . ' ' . $documentNumber . '</p>';
        $subheader = $subheader . '<p style="text-align: center">г. Волоколамск</p>';
        $subheader = $subheader . '<p style="font-weight: bold;font-style: italic">"' . $content->getName() . '"</p><br>';
        $documentPreambula = '<span style="text-align: justify;text-align-last: left;">На основании ' . $content->getReason() . ', в соответствии с правилами приёма в ГБПОУ МО "ВАТ "Холмогорка" в 2022 году, в соответствии с контрольными цифрами приёма, </span>';
        $documentBody = '<p style="text-align: justify; text-align-last: left;">1. Зачислить с 01.09.2022 г. в число студентов ГБПОУ МО "ВАТ "Холмогорка" на 1 курс по очной форме обучения за счёт бюджетных ассигнований бюджета Московской области лиц, рекомендованных Приемной комиссией к зачислению и представивших оригиналы соответствующих документов согласно приложению 1 к Приказу.</p>';

        $documentBody = $documentBody . "<br><br> <span style='font-weight: bold;font-style: italic'>Основание:</span> " . $content->getReason();
        $documentBody = $documentBody . '<br><br>';

        $pages[] = $this->newpage($header . '<hr style="margin: 0;padding: 0;height: 8px;border: none;border-top: 4px solid #333; border-bottom: 2px solid #333;">' . $subheader . '' . $documentPreambula . '<span style="text-transform:lowercase;font-weight: bold;letter-spacing: 3px">Приказываю:</span><br><br>' . $documentBody);
        $header = '<div style="margin-left:12cm;text-align: justify"><span >Приложение 1 к приказу <br> от ' . $createDate . ' г. № ' . $content->getNumber() . '</span></div><br>';
        $rc = array();

        foreach ((array)$content->getStudent()->getValues() as $data) {
            $rc[$data->getStudentGroup()->getId()][] = $data;
        }
        $itter = 0;
        foreach ($rc as $group) {
            $currgroup = $this->StudentGroupRepo->findOneBy(['id' => key($rc)]);

            dump($currgroup);

            $itter = 1 + $itter;
            $groupHeader = '<p style="font-weight: bold">' . $itter . '. Специальность ' . $currgroup->getFaculty()->getSpecialization()->getCode()
                . ' ' . $currgroup->getFaculty()->getName() . ' <span style="text-transform: lowercase"> ' . $currgroup->getFaculty()->getEducationForm()->getName() . ' форма обучения</span> '
                . ' <span style=""> ('.$currgroup->getFaculty()->getEducationType()->getName() . ')</span> '
            . '</p>';
            $groupList = '';
            $inGroupItter = 0;
            dump($group);
            foreach ($group as $student) {
                $inGroupItter = $inGroupItter + 1;
                $groupList = $groupList . '<p style="line-height:8px">' . $inGroupItter . '. ' . $student->getLastName() . ' ' . $student->getFirstName() . ' ' . $student->getMiddleName() . '</p>';
            }
            dump($groupHeader . $groupList);
            $addPage = $groupHeader . '<br>' . $groupList;

        }
        $pages[] = $this->newpage($header . $addPage);
        $result = implode('', $pages);

        return $result;
    }
}