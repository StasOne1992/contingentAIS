<?php

namespace App\Service;

use App\Entity\AdmissionPlan;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Student;
use App\Repository\AbiturientPetitionRepository;
use App\Repository\AdmissionPlanRepository;
use App\Repository\ContingentDocumentRepository;
use App\Repository\CollegeRepository;
use App\Repository\StudentGroupsRepository;


class TypicalDocuments
{
    private $college;
    private $collegeRepo;

    public function __construct(
        CollegeRepository                    $collegeRepository,
        StudentGroupsRepository              $StudentGroupRepo,
        private AbiturientPetitionRepository $abiturientPetitionRepository,
    )
    {
        $this->collegeRepo = $collegeRepository;
        $this->StudentGroupRepo = $StudentGroupRepo;

    }

    /**
     * @param bool $logo Включить ли логотип
     * @return string
     */
    public function getHeader($logo = true)
    {
        $collegeName = str_replace("\r\n", '<br>', $this->college->getFullName());
        $collegeAddress = '<p style="text-align: center;font-size: small">' . $this->college->getPostalAddress() . '</p>';
        $collegeContacts = '<p style="text-align: center;font-size: small">' . 'Телефон:' . $this->college->getPhone() . ' Факс:' . $this->college->getFax() . '<br>' . ' e-mail:' . $this->college->getEmail() . ' ' . $this->college->getWebSite() . '</p>';
        if ($logo) {

            $image = $this->college->getLogo();
            /*$type = pathinfo($image, PATHINFO_EXTENSION);
            $data = file_get_contents($image);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);*/
            $logo=' <img src="data:image/png;base64,'. base64_encode(file_get_contents('https://'.$_SERVER['HTTP_HOST'].$image)).'">';
            #$logo = '<img style="display: block; height:2cm; margin: 0 auto;" src="' . base64_encode($image). '">';

        } else {
            $logo = '';
        }
        $header = $logo . '<p style="text-align: center"><b>' . $collegeName . '</b></p>' . $collegeAddress . $collegeContacts;
        return $header;
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
        $header = $this->getHeader(false);
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
            /**
             * @var Student $data
             */

            $rc[$data->getStudentGroup()->getId()][] = $data;
        }
        $itter = 0;
        foreach ($rc as $group) {
            $currgroup = $this->StudentGroupRepo->findOneBy(['id' => key($rc)]);
            $itter = 1 + $itter;
            $groupHeader = '<p style="font-weight: bold">' . $itter . '. Специальность ' . $currgroup->getFaculty()->getSpecialization()->getCode()
                . ' ' . $currgroup->getFaculty()->getName() . ' <span style="text-transform: lowercase"> ' . $currgroup->getFaculty()->getEducationForm()->getTitle() . ' форма обучения</span> '
                . ' <span style=""> (' . $currgroup->getFaculty()->getEducationType()->getTitle() . ')</span> '
                . '</p>';
            $groupList = '';
            $inGroupItter = 0;
            foreach ($group as $student) {
                $inGroupItter = $inGroupItter + 1;
                $groupList = $groupList . '<p style="line-height:8px">' . $inGroupItter . '. ' . $student->getLastName() . ' ' . $student->getFirstName() . ' ' . $student->getMiddleName() . '</p>';
            }
            $addPage = $groupHeader . '<br>' . $groupList;

        }
        $pages[] = $this->newpage($header . $addPage);
        $result = implode('<div class="page_break"></div>', $pages);

        return $result;
    }


    /***
     * @param $admissionPlanPosition AdmissionPlan
     * @return void
     */
    public function generateAdmissionExaminationResultReport($admissionPlanPosition)
    {
        $pages = array();
        $this->setCollege(1);
        $header = $this->getHeader(false);

        $subheader = '<h2 style="text-align: center;text-transform: uppercase; margin: 0px">Протокол</h2><h3 style="margin: 0px;text-align: center;line-height: 1"">вступительного испытания</h3><br>';
        $createDate = date('d.m.Y');
        $documentNumber = 1;
        $subheader = $subheader .
            '<table  style="width: 100%;margin-bottom: 20px">
            <tr>
                <td style=" border: 0px; "> от ' . $createDate . '</td>
                <td style=" border: 0px; "> </td>
                <td style=" border: 0px; "> № ' . $documentNumber . '</td>
            </tr>
            </table>
            ';
        $admissionPlanPosition->getFaculty();
        $subheader = $subheader . '<h4 style="text-align: center;">Вступительные испытания по специальности ' . $admissionPlanPosition->getFaculty()->getSpecialization()->getCode() . ' ' . $admissionPlanPosition->getFaculty() . '<hr><span style="font-size: 10px">(наименование вступительного испытания)</span> </h4>';
        $body = '
            <table border="1" style="width: 100%;margin-bottom: 20px;margin-top: 30px;font-size: 12px; padding-bottom: 20px;">

            <tr>
            <th style="width: 5%">№ п/п</th>
            <th style="width: 30%">ФИО поступающего</th>';
        $examination = array();
        foreach ($admissionPlanPosition->getAdmissionExaminations() as $admissionExamination) {
            $examination[] = $admissionExamination;
            $body = $body . '<th style="width: 20%">Результат вступительного испытания <br>' . $admissionExamination->getExaminationSubject() . ', балл</th>';
        }
        $body = $body . '<th style="width: 25%">Подписи председателя и членов экзаменационной комиссии</th>
            </tr>

            ';
        $itter = 1;
        $countPass = 0;
        $countNotPass = 0;
        $countIsRemoved = 0;
        $abiturientPetition = $this->abiturientPetitionRepository->findBy(['AdmissionPlanPosition' => $admissionPlanPosition]);
        foreach ($abiturientPetition as $petition) {
            $currentcountNotPass = 0;
            $currentcountPass = 0;
            $body = $body . '<tr><td  style="width: 5%">' . $itter . '</td>';
            $body = $body . '<td align="left" style="width: 30%">' . $petition->getLastName() . ' ' . $petition->getFirstName() . ' ' . $petition->getMiddleName() . '</td>';
            foreach ($examination as $examinationitem) {
                foreach ($petition->getResult()->getValues() as $ExaminationResult) {
                    if ($examinationitem == $ExaminationResult->getAdmissionExamination()) {
                        $body = $body . '<td  align="center" style="width: 20%">';
                        if ($ExaminationResult->getMark() != 0) {
                            $body = $body . $ExaminationResult->getMark();
                            $currentcountPass = $currentcountPass + 1;
                        } elseif ($ExaminationResult->getMark() < $ExaminationResult->getAdmissionExamination()->getPassScore() and $ExaminationResult->getMark() != 0) {
                            $body = $body . 'не зачтено';
                            $currentcountPass = $currentcountPass + 1;
                        } else {
                            $body = $body . 'неявка';
                            $currentcountNotPass = $currentcountNotPass + 1;
                        }

                        $body = $body . '</td>';
                    }
                }
            }
            $body = $body . '<td style="width: 25%"> </td></tr>';
            $itter = $itter + 1;
            if ($currentcountPass >= 1) {
                $countPass = $countPass + 1;
            }
            if ($currentcountNotPass >= 1) {
                $countNotPass = $countNotPass + 1;
            }
        }

        $body = $body . '</tbody></table>';
        $body = $body . '<p>Число поступающих, явившихся на вступительное испытание: ' . $countPass . ' </p>';
        $body = $body . '<p>Число поступающих, неявившихся на вступительное испытание: ' . $countNotPass . '</p>';
        $body = $body . '<p>Число поступающих, удаленных с места проведения вступительного испытания: ' . $countIsRemoved . '</p>';
        $body = $body . '<br>';

        $body = $body . '<table class="no-border" style="width: 100%;border: 0px">
        <tr>
            <td class=no-border" style="width:60 %" colspan="3">Председатель экзаменационной комиссии:</td>
        </tr>
        <tr>
            <td style="width:60 % ;height: 40px; border-bottom: 1px solid black;text-align: center">Малахова Любовь Ивановна</td>
            <td style="width:10 %"></td>
            <td style="width:30 %; border-bottom: 1px solid black; "> </td>
        </tr>
            <tr>
                <td style="width:50 %"><p style="font-size: 8px;text-align: center">(фамилия, имя, отчество председателя экзаменационной комиссии)</p></td>
                <td  style="width:60px"> </td>
                <td  style="width:30 % "><p align="center" style="font-size: 8px;text-align: center">(подпись)</p></td>
            </tr>
             <tr>
            <td class=no-border" style="width:60 %" colspan="3">Члены экзаменационной комиссии:</td>
        </tr>';

        $comission[] = 'Гришина Ольга Валерьевна';
        $comission[] = 'Макарова Лариса Юрьевна';
        foreach ($comission as $item) {
            $body = $body . '<tr>
            <td style="width:60 % ;height: 30px; border-bottom: 1px solid black;text-align: center">' . $item . '</td>
            <td style="width:10 %"></td>
            <td style="width:30 % ; border-bottom: 1px solid black;"> </td>
        </tr>
       
                <tr>
            <td style="width:50 %"><p style="font-size: 8px;text-align: center">(фамилия, имя, отчество члена экзаменационной комиссии)</p></td>
            <td  style="width:40px"> </td>
            <td  style="width:30 % "><p align="center" style="font-size: 8px;text-align: center">(подпись)</p></td>
        </tr>';
        }


        $body = $body . '</table>';


        $pages[] = $header . $subheader . $body;
        $bodysummary = '';
        $footer = '';

        return implode('', $pages);;

    }


}