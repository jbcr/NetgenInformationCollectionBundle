<?php

namespace Netgen\Bundle\InformationCollectionBundle\Tests\Factory;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\FieldType\EmailAddress\Value as EmailValue;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\Helper\FieldHelper;
use Netgen\Bundle\InformationCollectionBundle\Factory\EmailDataFactory;
use Netgen\Bundle\InformationCollectionBundle\Value\EmailData;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class EmailDataFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EmailDataFactory
     */
    protected $factory;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolver;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldHelper;

    public function setUp()
    {
        $this->configResolver = $this->getMockBuilder(ConfigResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParameter', 'hasParameter', 'setDefaultNamespace', 'getDefaultNamespace'])
            ->getMock();

        $this->translationHelper = $this->getMockBuilder(TranslationHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTranslatedField'])
            ->getMock();

        $this->fieldHelper = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFieldEmpty'])
            ->getMock();

        $this->factory = new EmailDataFactory($this->configResolver, $this->translationHelper, $this->fieldHelper);
        parent::setUp();
    }

    public function testBuild()
    {
        $recipientField = new Field([
            'value' => new EmailValue('recipient@test.com'),
            'languageCode' => 'eng_GB',
            'fieldDefIdentifier' => 'recipient'
        ]);

        $senderField = new Field([
            'value' => new EmailValue('sender@test.com'),
            'languageCode' => 'eng_GB',
            'fieldDefIdentifier' => 'sender'
        ]);

        $subjectField = new Field([
            'value' => new TextLineValue('subject test'),
            'languageCode' => 'eng_GB',
            'fieldDefIdentifier' => 'subject',
        ]);

        $content = new Content([
            'internalFields' => [
                $recipientField, $senderField, $subjectField,
            ],
        ]);

        $this->fieldHelper->expects($this->exactly(3))
            ->method('isFieldEmpty')
            ->withAnyParameters()
            ->willReturn(false);


        $this->translationHelper->expects($this->at(0))
            ->method('getTranslatedField')
            ->with($content, 'recipient')
            ->willReturn($recipientField);


        $this->translationHelper->expects($this->at(1))
            ->method('getTranslatedField')
            ->with($content, 'sender')
            ->willReturn($senderField);

        $this->translationHelper->expects($this->at(2))
            ->method('getTranslatedField')
            ->with($content, 'subject')
            ->willReturn($subjectField);

        $this->configResolver->expects($this->any())
            ->method('getParameter')
            ->with('information_collection.email.template', 'netgen')
            ->willReturn('template');

        $value = $this->factory->build($content);

        $this->assertInstanceOf(EmailData::class, $value);
        $this->assertEquals('recipient@test.com', $value->getRecipient());
        $this->assertEquals('sender@test.com', $value->getSender());
        $this->assertEquals('subject test', $value->getSubject());
        $this->assertEquals('template', $value->getTemplate());
    }

    public function testBuildWithFieldMissingInContent()
    {
        $recipientField = new Field([
            'value' => new EmailValue('recipient@test.com'),
            'languageCode' => 'eng_GB',
            'fieldDefIdentifier' => 'test1'
        ]);

        $senderField = new Field([
            'value' => new EmailValue('sender@test.com'),
            'languageCode' => 'eng_GB',
            'fieldDefIdentifier' => 'test2'
        ]);

        $subjectField = new Field([
            'value' => new TextLineValue('subject test'),
            'languageCode' => 'eng_GB',
            'fieldDefIdentifier' => 'test3',
        ]);

        $content = new Content([
            'internalFields' => [
                $recipientField, $senderField, $subjectField,
            ],
        ]);

        $this->fieldHelper->expects($this->never())
            ->method('isFieldEmpty');

        $this->translationHelper->expects($this->never())
            ->method('getTranslatedField');


        $this->translationHelper->expects($this->never())
            ->method('getTranslatedField');

        $this->translationHelper->expects($this->never())
            ->method('getTranslatedField');

        $this->configResolver->expects($this->at(0))
            ->method('getParameter')
            ->with('information_collection.email.recipient', 'netgen')
            ->willReturn('recipient@test.com');

        $this->configResolver->expects($this->at(1))
            ->method('getParameter')
            ->with('information_collection.email.sender', 'netgen')
            ->willReturn('sender@test.com');

        $this->configResolver->expects($this->at(2))
            ->method('getParameter')
            ->with('information_collection.email.subject', 'netgen')
            ->willReturn('subject test');


        $this->configResolver->expects($this->at(3))
            ->method('getParameter')
            ->with('information_collection.email.template', 'netgen')
            ->willReturn('template');

        $value = $this->factory->build($content);

        $this->assertInstanceOf(EmailData::class, $value);
        $this->assertEquals('recipient@test.com', $value->getRecipient());
        $this->assertEquals('sender@test.com', $value->getSender());
        $this->assertEquals('subject test', $value->getSubject());
        $this->assertEquals('template', $value->getTemplate());
    }
}