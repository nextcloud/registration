<?php

namespace OCA\Registration\Util;

class CoreBridge
{
    /**
     * This function maps an exception class to the available exception class of the core
     * in order to provide cross-core and cross-version compatibility.
     *
     * @param string $className
     * @return string
     * @throws \LogicException
     */
    public static function exceptionClass($className)
    {
        static $classes = [
            'OCSException'           => [
                'OCP\AppFramework\OCS\OCSException',
                'OC\OCS\Exception',
            ],
            'OCSBadRequestException' => [
                'OCP\AppFramework\OCS\OCSBadRequestException',
                'OC\OCS\Exception',
            ],
            'OCSNotFoundException'   => [
                'OCP\AppFramework\OCS\OCSNotFoundException',
                'OC\OCS\Exception',
            ],
            'DoesNotExistException'  => [
                'OCP\AppFramework\Db\DoesNotExistException',
                'OCP\AppFramework\Db\DoesNotExistException',
            ],
        ];

        if (!array_key_exists($className, $classes)) {
            throw new \LogicException('No valid exception class found');
        }

        foreach ($classes[$className] as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \LogicException('No valid exception class found');
    }

    /**
     * @param string      $className
     * @param null|string $message
     * @param null|int    $code
     * @return \Exception
     */
    public static function createException($className, $message = null, $code = null)
    {
        $exceptionClassName = self::exceptionClass($className);

        $reflection = new \ReflectionClass($exceptionClassName);
        $params = $reflection->getConstructor()->getParameters();

        if ($params[0]->getClass() && ($params[0]->getClass()->getName() === 'OC\OCS\Result' || $params[0]->getClass()->getName() === 'OC_OCS_Result')) {
            $subClass = $params[0]->getClass()->getName();
            return new $exceptionClassName(new $subClass($message, $code));
        }

        if (count($params) >= 2) {
            if ($params[1]->getClass() && $params[1]->getClass()->getName() === 'Exception') {
                return new $exceptionClassName($message);
            }

            return new $exceptionClassName($message, $code);
        }

        if ($exceptionClassName === 'OCP\AppFramework\OCS\OCSNotFoundException') {
            return new $exceptionClassName($message);
        }

        return new $exceptionClassName();
    }
}
