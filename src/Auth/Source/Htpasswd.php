<?php

/**
 * Authentication source for Apache 'htpasswd' files.
 *
 * @package SimpleSAMLphp
 */

declare(strict_types=1);

namespace SimpleSAML\Module\authcrypt\Auth\Source;

use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\{Error, Logger, Utils};
use WhiteHat101\Crypt\APR1_MD5;

class Htpasswd extends \SimpleSAML\Module\core\Auth\UserPassBase
{
    /**
     * Our users, stored in an array, where each value is "<username>:<passwordhash>".
     *
     * @var array
     */
    private array $users;

    /**
     * An array containing static attributes for our users.
     *
     * @var array
     */
    private array $attributes = [];


    /**
     * Constructor for this authentication source.
     *
     * @param array $info Information about this authentication source.
     * @param array $config Configuration.
     *
     * @throws \Exception if the htpasswd file is not readable or the static_attributes array is invalid.
     */
    public function __construct(array $info, array $config)
    {
        // Call the parent constructor first, as required by the interface
        parent::__construct($info, $config);

        $this->users = [];

        if (!$htpasswd = file_get_contents($config['htpasswd_file'])) {
            throw new Exception('Could not read ' . $config['htpasswd_file']);
        }

        $this->users = explode("\n", trim($htpasswd));

        try {
            $attrUtils = new Utils\Attributes();
            $this->attributes = $attrUtils->normalizeAttributesArray($config['static_attributes']);
        } catch (Exception $e) {
            throw new Exception('Invalid static_attributes in authentication source ' .
                $this->authId . ': ' . $e->getMessage());
        }
    }


    /**
     * Attempt to log in using the given username and password.
     *
     * On a successful login, this function should return the username as 'uid' attribute,
     * and merged attributes from the configuration file.
     * On failure, it should throw an exception. A \SimpleSAML\Error\Error('WRONGUSERPASS')
     * should be thrown in case of a wrong username OR a wrong password, to prevent the
     * enumeration of usernames.
     *
     * @param string $username The username the user wrote.
     * @param string $password The password the user wrote.
     *
     * @return array Associative array with the users attributes.
     *
     * @throws \SimpleSAML\Error\Error if authentication fails.
     */
    protected function login(string $username, string $password): array
    {
        $cryptoUtils = new Utils\Crypto();
        foreach ($this->users as $userpass) {
            $matches = explode(':', $userpass, 2);
            if ($matches[0] == $username) {
                $crypted = $matches[1];

                // This is about the only attribute we can add
                $attributes = array_merge(['uid' => [$username]], $this->attributes);

                // Traditional crypt(3)
                if ($cryptoUtils->secureCompare($crypted, crypt($password, $crypted))) {
                    Logger::debug('User ' . $username . ' authenticated successfully');
                    Logger::warning(
                        'CRYPT authentication is insecure. Please consider using something else.'
                    );
                    return $attributes;
                }

                // Apache's custom MD5
                if (APR1_MD5::check($password, $crypted)) {
                    Logger::debug('User ' . $username . ' authenticated successfully');
                    Logger::warning(
                        'APR1 authentication is insecure. Please consider using something else.'
                    );
                    return $attributes;
                }

                // PASSWORD_BCRYPT
                if ($cryptoUtils->pwValid($crypted, $password)) {
                    Logger::debug('User ' . $username . ' authenticated successfully');
                    return $attributes;
                }
                throw new Error\Error('WRONGUSERPASS');
            }
        }
        throw new Error\Error('WRONGUSERPASS');
    }
}
