<?php
use Drupal\loft_core\Attribute;

class LoftCoreTest extends PHPUnit_Framework_TestCase {

    /**
     * Provides data for test_loft_core_users_get_email_domain.
     */
    function DataForTest_loft_core_users_get_email_domainProvider() {
      $tests = array();
      $tests[] = array(
          'brainboostingsupplements.org', 'heath@e.brainboostingsupplements.org'
      );

      return $tests;
    }

    /**
     * @dataProvider DataForTest_loft_core_users_get_email_domainProvider
     */
    public function test_loft_core_users_get_email_domain($control, $mail)
    {
        $this->assertSame($control, _loft_core_users_get_email_domain($mail));
    }

    public function testLoftCoreTestClass()
    {
        $this->assertEmpty(loft_core_test_class('golden'));
        define('DRUPAL_ENV_ROLE', 'staging');
        $this->assertSame('t-golden', loft_core_test_class('golden'));
    }

    /**
     * Provides data for testLoftCoreCl.
     */
    function DataForTestLoftCoreClProvider()
    {
        $tests = array();
        $tests[] = array(
            'do',
            're',
            'do__re',
            'do--re',
        );
        $tests[] = array(
            'a_list_apart',
            'chapter-one-thing',
            'a-list-apart__chapter-one-thing',
            'a-list-apart--chapter-one-thing',
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestLoftCoreClProvider
     */
    public function testLoftCoreCl($base, $subject, $control_component, $control_version)
    {
        $cl = loft_core_cl($base);
        $this->assertSame($control_component, $cl($subject));
        $this->assertSame($control_component, $cl($subject, true));
        $this->assertSame($control_version, $cl($subject, false));
    }

    public function testLoftCoreTabIndexWithObject()
    {
        $tabindex = $ti_control = 100;
        $el = ['#attributes' => new Attribute()];
        $control = ['tabindex' => 100];
        loft_core_form_tabindex($el, $tabindex);
        $this->assertSame($control, $el['#attributes']->toArray());
        $this->assertSame($ti_control + 1, $tabindex);
    }

    public function testLoftCoreTabIndexWithArray()
    {
        $tabindex = $ti_control = 100;
        $el = [];
        $control = [
            '#attributes' => ['tabindex' => 100],
        ];
        loft_core_form_tabindex($el, $tabindex);
        $this->assertSame($control, $el);
        $this->assertSame($ti_control + 1, $tabindex);
    }

    public function testRemovePartOfStyle()
    {
        $attributes = new Attribute(['style' => 'background-repeat:repeat;color:red;']);
        $attributes->removeStyle('background-repeat');
        $this->assertSame(' style="color:red"', strval($attributes));
        $this->assertTrue($attributes->hasStyle('color'));


    }

    public function testRemoveStyle()
    {
        $attributes = new Attribute(['style' => 'background-repeat:repeat']);
        $return = $attributes->removeStyle('background-repeat');
        $this->assertEmpty(strval($attributes));
        $this->assertSame($attributes, $return);
    }

    public function testAddStyleReplace()
    {
        $attributes = new Attribute(['style' => 'background-repeat:repeat']);
        $attributes->addStyle('background-repeat', 'no-repeat');
        $this->assertSame(' style="background-repeat:no-repeat"', strval($attributes));
    }

    public function testAddStyle()
    {
        $attributes = new Attribute;
        $return = $attributes->addStyle('background-repeat', 'no-repeat');
        $this->assertSame(' style="background-repeat:no-repeat"', strval($attributes));
        $this->assertSame($attributes, $return);
    }

    /**
     * Provides data for testMinWeight.
     */
    function DataForTestWeightProvider()
    {
        $tests = array();
        $tests[] = array(
            -10,
            9,
            array(
                array('#weight' => 0),
                array('#weight' => 9),
                array('#weight' => -5),
                array('#weight' => -10),
            ),
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestWeightProvider
     */
    public function testMaxWeight($min, $max, $subject)
    {
        $this->assertSame($max, loft_core_max_weight($subject));
    }

    /**
     * @dataProvider DataForTestWeightProvider
     */
    public function testMinWeight($min, $max, $subject)
    {
        $this->assertSame($min, loft_core_min_weight($subject));
    }

    /**
     * Provides data for testFormDisableElements.
     */
    function DataForTestFormDisableElementsProvider()
    {
        $tests = array();

        // Test One
        $form = array();
        $form['field_description']['und'][0] = array();
        $form['field_description']['und'][0]['#description'] = "Enter data";

        $control_form = $form;
        $control_form['field_description']['und'][0]['#disabled'] = true;
        $control_form['field_description']['und'][0]['#required'] = false;
        $control_form['field_description']['und'][0]['#description'] = "This field is controlled by the Loft Core module and cannot be modified in the UI.";

        $tests[] = array(
            $form,
            array('field_description.und.0'),
            null,
            null,
            $control_form,
        );

        // Test Two
        $form = array();
        $form['field_description']['und'][0] = array();
        $form['field_description']['und'][0]['#description'] = "Enter data";

        $control_form = $form;
        $control_form['field_description']['und'][0]['#disabled'] = true;
        $control_form['field_description']['und'][0]['#required'] = false;
        $control_form['field_description']['und'][0]['#description'] = "You can't enter data here, my friend.";

        $tests[] = array(
            $form,
            array('field_description.und.0'),
            'Test Bot',
            'You can\'t enter data here, my friend.',
            $control_form,
        );

        // Test Three
        $form = array();
        $form['field_description']['und'][0] = array();
        $form['field_description']['und'][0]['#description'] = "Enter data";

        $control_form = $form;
        $control_form['field_description']['und'][0]['#description'] = "This field is controlled by the Test Bot module and cannot be modified in the UI.";
        $control_form['field_description']['und'][0]['#required'] = false;
        $control_form['field_description']['und'][0]['#disabled'] = true;

        $tests[] = array(
            $form,
            array('field_description.und.0'),
            'Test Bot',
            null,
            $control_form,
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestFormDisableElementsProvider
     */
    public function testFormDisableElements($form, $paths, $module_name, $message, $control_form)
    {
        loft_core_form_disable_elements($form, $paths, $module_name, $message);
        $this->assertEquals($control_form, $form);
    }
}
