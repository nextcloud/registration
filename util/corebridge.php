<?php

namespace OCA\Registration\Util;

class CoreBridge
{
    /**
     * @param string $className
     * @return string
     * @throws \LogicException
     */
    public static function exceptionClass($className)
    {
        static $classes = [
            'OCSException'           => [
                'nextcloud' => 'OCP\AppFramework\OCS\OCSException',
                'owncloud'  => 'OC\OCS\Exception',
            ],
            'OCSBadRequestException' => [
                'nextcloud' => 'OCP\AppFramework\OCS\OCSBadRequestException',
                'owncloud'  => 'OC\OCS\Exception',
            ],
            'OCSNotFoundException'   => [
                'nextcloud' => 'OCP\AppFramework\OCS\OCSNotFoundException',
                'owncloud'  => 'OC\OCS\Exception',
            ],
            'DoesNotExistException'  => [
                'nextcloud' => 'OCP\AppFramework\Db\DoesNotExistException',
                'owncloud'  => 'OCP\AppFramework\Db\DoesNotExistException',
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

        if ($params[0]->getClass() && $params[0]->getClass()->getName() === 'OC\OCS\Result') {
            return new $exceptionClassName(new \OC\OCS\Result($message, $code));
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
