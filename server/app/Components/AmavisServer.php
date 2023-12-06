<?php


namespace App\Components;


use App\Exceptions\ExtendedException;

class AmavisServer
{
    /**
     * Name of the Amavis server
     * @var string
     */
    private $name = "";

    /**
     * IP of the Amavis server
     * @var string
     */
    private $ip = "";

    /**
     * SSH port of the Amavis server
     * @var string
     */
    private $sshPort = "";

    /**
     * SSH user of the Amavis server with sudo privileges
     * @var string
     */
    private $sshUser = "";

    /**
     * SSH password of the Amavis server
     * @var string
     */
    private $sshPassword = "";

    /**
     * Isolation path of the Amavis server
     * @var string
     */
    private $isolationPath = "";

    /**
     * Email of the Amavis server
     * @var string
     */
    private $email = "";

    /**
     * The email representation type of the Amaivs server
     * @var string
     */
    private $emailRepresentationType = "";

    /**
     * The default type an Amaivs server email address is represented in an "email was isolated message"
     */
    private const AMAVIS_SERVER_EMAIL_REPRESENTATION_KEY_DEFAULT = "DEFAULT";

    /**
     * The mailto type an Amaivs server email address is represented in an "email was isolated message"
     */
    private const AMAVIS_SERVER_EMAIL_REPRESENTATION_KEY_MAILTO = "MAILTO";

    /**
     * AmavisServer constructor
     * @param string $suffix
     * @throws ExtendedException
     */
    public function __construct(string $suffix) {

        // Set attributes
        $this->setConfig($suffix);
    }

    /**
     * Set the Amavis server config attributes
     * @param string $suffix
     * @throws ExtendedException
     */
    private function setConfig(string $suffix) : void {

        // Attributes of this class and their corresponding env constants
        $attributes = [
            [ "name" => "name", "envConstant" => "AMAVIS_SERVER_$suffix" ],
            [ "name" => "ip", "envConstant" => "AMAVIS_SERVER_IP_$suffix" ],
            [ "name" => "sshPort", "envConstant" => "AMAVIS_SERVER_SSH_PORT_$suffix" ],
            [ "name" => "sshUser", "envConstant" => "AMAVIS_SERVER_SSH_USERNAME_$suffix" ],
            [ "name" => "sshPassword", "envConstant" => "AMAVIS_SERVER_SSH_PASSWORD_$suffix" ],
            [ "name" => "isolationPath", "envConstant" => "AMAVIS_SERVER_ISOLATION_PATH_$suffix" ],
            [ "name" => "email", "envConstant" => "AMAVIS_SERVER_EMAIL_$suffix" ],
            [ "name" => "emailRepresentationType", "envConstant" => "AMAVIS_SERVER_EMAIL_REPRESENTATION_$suffix" ]
        ];

        foreach ($attributes as $attribute) {

            // Set the env value on the attribute
            $this->{$attribute["name"]} = env($attribute["envConstant"]);

            // Throw exception if at least one attribute not set
            if (strlen($this->{$attribute["name"]}) <= 0) {
                $envConstant = $attribute["envConstant"];
                throw new ExtendedException("Env constant '$envConstant' returned an empty value.");
            }
        }
    }

    /**
     * Get the representation of an email with the given representation type
     * @return string
     * @throws ExtendedException
     */
    private function getAmavisServerEmailRepresentation() : string {
        switch ($this->emailRepresentationType) {
            case self::AMAVIS_SERVER_EMAIL_REPRESENTATION_KEY_DEFAULT:
                return "<$this->email>";
            case self::AMAVIS_SERVER_EMAIL_REPRESENTATION_KEY_MAILTO:
                return "[mailto:$this->email]";
            default:
                throw new ExtendedException("Invalid Amavis server email representation type " .
                    "'$this->emailRepresentationType'.");
        }
    }

    /**
     * Get the representation of an Amaivs server in an "email was isolated message"
     * @return string
     * @throws ExtendedException
     */
    public function getAmavisServerRepresentation() : string {
        return "$this->name " . $this->getAmavisServerEmailRepresentation();
    }

    /**
     * Get the Amavis server name
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * Get the Amavis server ip
     * @return string
     */
    public function getIp() : string {
        return $this->ip;
    }

    /**
     * Get the Amavis server SSH port
     * @return string
     */
    public function getSshPort() : string {
        return $this->sshPort;
    }

    /**
     * Get the Amavis server SSH user
     * @return string
     */
    public function getSshUsername() : string {
        return $this->sshUser;
    }

    /**
     * Get the Amavis server SSH password
     * @return string
     */
    public function getSshPassword() : string {
        return $this->sshPassword;
    }

    /**
     * Get the Amavis server isolation path
     * @return string
     */
    public function getIsolationPath() : string {
        return $this->isolationPath;
    }
}
