<?php

class NotFoundRowException extends RuntimeException
{
    protected $message = "Не найдена запись в базе данных";
}