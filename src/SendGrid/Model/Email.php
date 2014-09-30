<?php

namespace SendGrid\Model;


class Email
{
    /**
     * @var array
     */
    protected $to = array();

    /**
     * @var string
     */
    protected $from = null;

    /**
     * @var string|bool
     */
    protected $fromName = false;

    /**
     * @var bool|string
     */
    protected $replyTo = false;

    /**
     * @var array
     */
    protected $ccList = array();

    /**
     * @var array
     */
    protected $bccList = array();

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var null|array
     */
    protected $headers = null;

    /**
     * @var array
     */
    protected $attachments = array();

    /**
     * @var array
     */
    protected $substitutions = array();

    /**
     * @var array
     */
    protected $uniqueArgs = array();

    /**
     * @var array
     */
    protected $categories = array();

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var array
     */
    protected $sections = array();

    public function __construct($mixed = null)
    {
        if (is_array($mixed) || $mixed instanceof \stdClass || $mixed instanceof \Traversable) {
            $this->bulkSetter($mixed);
        }
    }

    /**
     * @param \stdClass $obj
     * @return $this
     */
    public function bulkSetter($obj)
    {
        foreach ($obj as $prop => $val) {
            $setter = 'set'.implode(
                    '',
                    array_map(
                        'ucfirst',
                        explode(
                            '_',
                            strtolower(
                                trim(
                                    $prop
                                )
                            )
                        )
                    )
                );
            if (method_exists($this, $setter)) {
                $this->{$setter}($val);
            }
        }
        return $this;
    }

    /**
     * @param array $sections
     * @return $this
     */
    public function setSections(array $sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * @param $from
     * @param $to
     * @return $this
     */
    public function addSection($from, $to)
    {
        $this->sections[$from] = $to;
        return $this;
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @param $name
     * @param $paramName
     * @param $value
     * @return $this
     */
    public function addFilter($name, $paramName, $value)
    {
        if (!isset($this->filters[$name])) {
            $this->filters[$name] = array(
                'settings'  => array()
            );
        } elseif (!isset($this->filters[$name]['settings'])) {
            $this->filters[$name]['settings'] = array();
        }
        $this->filters[$name]['settings'][$paramName] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @param $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $uArgs
     * @return $this
     */
    public function setUniqueArgs(array $uArgs)
    {
        $this->uniqueArgs = $uArgs;
        return $this;
    }

    /**
     * @param $name
     * @param $val
     * @return $this
     */
    public function addUniqueArg($name, $val)
    {
        $this->uniqueArgs[$name] = $val;
        return $this;
    }

    /**
     * @return array
     */
    public function getUniqueArgs()
    {
        return $this->uniqueArgs;
    }

    /**
     * @param array $subs
     * @return $this
     */
    public function setSubstitutions(array $subs)
    {
        $this->substitutions = $subs;
        return $this;
    }

    /**
     * @param $from
     * @param array $to
     * @return $this
     */
    public function addSubstitution($from, array $to)
    {
        $this->substitutions[$from] = $to;
        return $this;
    }

    /**
     * @return array
     */
    public function getSubstitutions()
    {
        return $this->substitutions;
    }

    /**
     * @param array $attachments
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $files = array();
        foreach ($attachments as $name => $path) {
            if (!is_numeric($name)) {
                $files[$path] = $this->getFileInfo(
                    $path,
                    $name
                );
            } else {
                $files[$path] = $this->getFileInfo(
                    $path
                );
            }
        }
        $this->attachments = $files;
        return $this;
    }

    /**
     * @param $file
     * @param null|string $name = null
     * @return mixed
     */
    protected function getFileInfo($file, $name = null)
    {
        $info = pathinfo($file);
        $info['file'] = $file;
        if ($name !== null) {
            $info['custom_filename'] = $name;
        }
        return $info;
    }

    public function addAttachment($file, $name = null)
    {
        $this->attachments[$file] = $this->getFileInfo(
            $file,
            $name
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function removeAttachment($file)
    {
        if (isset($this->attachments[$file])) {
            unset($this->attachments[$file]);
        }
        return $this;
    }

    /**
     * @param string|array $bcc
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addBcc($bcc)
    {
        if (is_array($bcc)) {
            $bcc = $this->createEmailArray(
                $bcc
            );
            $bcc = $bcc[0];
        } else {
            if (!filter_var($bcc, \FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s is not a valid email address',
                        $bcc
                    )
                );
            }
        }
        $this->bccList[] = $bcc;
        return $this;
    }

    /**
     * @param array $bccList
     * @param bool $strict = false
     * @return $this
     */
    public function setBccList($bccList, $strict = false)
    {
        $this->bccList = $this->createEmailArray(
            $bccList,
            $strict
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getBccList()
    {
        return $this->bccList;
    }

    /**
     * @param string|array $cc
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addCc($cc)
    {
        if (is_array($cc)) {
            $cc = $this->createEmailArray(
                $cc
            );
            $cc = $cc[0];
        } else {
            if (!filter_var($cc, \FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s is not a valid email address',
                        $cc
                    )
                );
            }
        }
        $this->ccList[] = $cc;
        return $this;
    }

    /**
     * @param array $ccList
     * @param bool $strict
     * @return $this
     */
    public function setCcList($ccList, $strict = false)
    {
        $this->ccList = $this->createEmailArray(
            $ccList,
            $strict
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getCcList()
    {
        return $this->ccList;
    }

    /**
     * @param array $from
     * @return $this
     */
    public function setFrom($from)
    {
        if (is_array($from)) {
            foreach ($from as $name => $mail) {
                $this->fromName = $name;
                return $this->setFrom($mail);
            }
        }
        if (!filter_var($from, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s is an invalid from address',
                    $from
                )
            );
        }
        $this->from = $from;
        return $this;
    }

    /**
     * @param bool $asArray = false
     * @return array|string
     */
    public function getFrom($asArray = false)
    {
        if ($asArray === true) {
            return array(
                $this->from => $this->fromName
            );
        }
        return $this->from;
    }

    /**
     * @return string
     */
    public function getFromString()
    {
        if (!$this->fromName) {
            return $this->from;
        }
        return sprintf(
            '%s <%s>',
            $this->fromName,
            $this->from
        );
    }

    /**
     * @param boolean $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param null $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return null
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function removeHeader($name)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
        return $this;
    }

    /**
     * @return string
     */
    protected function getHeadersJson()
    {
        if (!$this->headers) {
            return '{}';
        }
        return json_encode(
            $this->headers,
            \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_HEX_AMP
        );
    }

    /**
     * @param string $html
     * @return $this
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param boolean $replyTo
     * @return $this
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = strip_tags($text);
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param $email
     * @param null $name
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addTo($email, $name = null)
    {
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );
        }
        if ($name) {
            $email = sprintf(
                '%s <%s>',
                $name,
                $email
            );
        }
        $this->to[] = $email;
        return $this;
    }

    /**
     * @param array $to
     * @return $this
     */
    public function setTo(array $to)
    {
        $this->to = $this->createEmailArray(
            $to
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    protected function generateTextContent()
    {
        $dom = new \DOMDocument();
        $html = preg_replace(
            '/<\s*br\s*\/?\s*>/i',
            PHP_EOL,
            $this->html
        );
        $dom->loadHTML(
            $html
        );
        /** @var \DOMElement $body */
        $body = $dom->getElementsByTagName('body')->item(0);
        $nodes = $body->getElementsByTagName('*');//get nodes
        $txt = array();
        foreach ($nodes as $node) {
            if ($node->firstChild->nodeType === XML_TEXT_NODE)
                $txt[] = $node->textContent;
        }
        return implode("\n\r", $txt);
    }

    /**
     * Helper method, used to set to, cc, bcc, and from addresses...
     * @param array $addresses
     * @param bool $throwException = true
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function createEmailArray(array $addresses, $throwException = true)
    {
        $formatted = array();
        foreach ($addresses as $key => $mail) {
            if (!filter_var($mail, \FILTER_VALIDATE_EMAIL)) {
                if ($throwException) {
                    throw new \InvalidArgumentException(
                        '%s is an invalid email address',
                        $mail
                    );
                }
            } else {
                if (!is_numeric($key)) {
                    $mail = sprintf(
                        '%s <%s>',
                        $key,
                        $mail
                    );
                }
                $formatted[] = $mail;
            }
        }
        return $formatted;
    }

    /**
     * @return array
     */
    public function toWebFormat()
    {
        if (!$this->text) {
            $this->setText(
                $this->generateTextContent()
            );
        }
        $data = array(
            'x-smtpapi' => $this->getSmtpApi(),
            'subject'   => $this->getSubject(),
            'html'      => $this->getHtml(),
            'headers'   => $this->getHeadersJson(),
            'text'      => $this->text
        );
        $data['to'] = $data['from'] = $this->getFrom();

        $add = array(
            'cc'        => $this->getCcList(),
            'bcc'       => $this->getBccList(),
            'fromname'  => $this->getFromName(),
            'replyto'   => $this->getReplyTo()
        );
        foreach ($add as $key => $val) {
            if ($val) {
                $data[$key] = $val;
            }
        }
        if ($this->attachments) {
            foreach ($this->getAttachmentArray() as $key => $v) {
                $data[$key] = $v;
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function getAttachmentArray()
    {
        $files = array();
        foreach ($this->attachments as $file => $info) {
            $fName = $info['filename'];
            $fullName = isset($info['custom_filename']) ? $info['custom_filename'] : $fName;
            $extension = isset($info['extension']) ? $info['extension'] : null;
            if (class_exists('CurlFile', false)) {
                $contents = new \CurlFile(
                    $file,
                    $extension,
                    $fName
                );
            } else {
                $contents = '@'.$file;
            }
            $files['files['.$fullName.']'] = $contents;
        }
        return $files;
    }

    /**
     * @return string
     */
    protected function getSmtpApi()
    {//x-smtpapi
        $data = array(
            'to'            => 'getTo',
            'sub'           => 'getSubstitutions',
            'unique_args'   => 'getUniqueArgs',
            'category'      => 'getCategories',
            'section'       => 'getSections',
            'filters'       => 'getFilters'
        );
        $json = array();
        foreach ($data as $key => $getter) {
            $val = $this->{$getter}();
            if ($val) {
                $json[$key] = $val;
            }
        }
        if (!$json) {
            return '{}';
        }
        $json_string = json_encode(
            $json,
            \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_HEX_AMP
        );
        return str_replace('\\/', '/', $json_string);
    }

}