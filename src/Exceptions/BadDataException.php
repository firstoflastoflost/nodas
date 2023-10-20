<?php

class BadDataException extends RuntimeException
{
    protected $message = "Несоответствие типов переданных данных";
}