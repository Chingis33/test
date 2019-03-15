<?php

namespace App\Service;

/**
 * Хелпер для сервиса.
 * Наверное переменную $endingAt можно было по разному передать, но раз четко не указано...
 * написано в стиле 7.0, точно не указано, что должны возвращать методы, поэтому они пустые.
 *
 */
class ServiceHelper
{

    /**
     * Расчет количества дней срока действия услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function countOfDaysExpiration (string $endingAt): string
    {
        $countOfDays = 'как то мы получаем дни' . $endingAt;
        return $countOfDays;
    }

    /**
     * Определение статуса активности услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function getStatusOfActivity (string $endingAt): string
    {

    }

    /**
     * Определение статуса начала завершения услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function getStatusOfEndingBegin (string $endingAt): string
    {

    }

    /**
     * Расчет количества дней удаления услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function calculateDaysOfRemoval (string $endingAt): string
    {

    }

    /**
     * Определение статуса удаления услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function getStatusOfRemoval (string $endingAt): string
    {

    }

    /**
     * Расчет даты окончательного удаления услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function calculateDateOfFinalRemoval (string $endingAt): string
    {

    }

    /**
     * Расчет даты окончательного удаления услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function getDateOfFinalRemoval (string $endingAt): string
    {

    }

    /**
     * Определение статуса окончательного удаления услуги.
     *
     * @param string $endingAt
     * @return string
     */
    public function getStatusOfFinalRemoval (string $endingAt): string
    {

    }

}
