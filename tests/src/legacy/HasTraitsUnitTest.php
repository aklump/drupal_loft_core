<?php

namespace Drupal\Tests\loft_core\Unit;

use AKlump\DrupalTest\Drupal8\UnitTestCase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\loft_core\Entity\HasEntityInterface;
use Drupal\loft_core\Entity\HasEntityTrait;
use Drupal\loft_core\Entity\HasNodeInterface;
use Drupal\loft_core\Entity\HasNodeTrait;
use Drupal\loft_core\Entity\HasUserInterface;
use Drupal\loft_core\Entity\HasUserTrait;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Tests basic Loft Core functionality at the Unit level.
 *
 * @group loft_core
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class HasTraitsUnitTest extends UnitTestCase {

  public function testOnSetSwapsNewEntity() {
    global $new_entity_to_swap;

    $entity = \Mockery::mock(EntityInterface::class);
    $entity->allows('getEntityTypeId')->andReturn('image_style');
    $entity->allows('bundle')->andReturn('');
    $entity->allows('id')->andReturn(45);

    $new_entity_to_swap = \Mockery::mock(EntityInterface::class);
    $new_entity_to_swap->allows('getEntityTypeId')->andReturn('image_style');
    $new_entity_to_swap->allows('bundle')->andReturn('');
    $new_entity_to_swap->allows('id')->andReturn(45);

    $obj = new OnSetSwap();
    $obj->setEntity($entity);

    $this->assertNotSame($new_entity_to_swap, $entity);
    $this->assertSame($new_entity_to_swap, $obj->getEntity('image_style'));
  }

  /**
   * @expectedException \UnexpectedValueException
   */
  public function testOnSetEntityReturnStringThrows() {
    $entity = \Mockery::mock(EntityInterface::class);
    $entity->allows('getEntityTypeId')->andReturn('image_style');
    $entity->allows('bundle')->andReturn('');
    $entity->allows('id')->andReturn(45);

    $obj = new BadClass();
    $obj->setEntity($entity);
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testRequireEntityWithEmptyStringThrows() {
    $obj = new HasDefaultEntityType();
    $obj->ipsum();
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\IncompleteImplementationException
   */
  public function testMissingClassConstantIsThrown() {
    $obj = new EntityManipulator();
    $obj->getEntity();
  }

  public function testClassWithEntityTypeIdConstantAllowsNoArguments() {
    $entity = \Mockery::mock(EntityInterface::class);
    $entity->allows('getEntityTypeId')->andReturn('image_style');
    $entity->allows('bundle')->andReturn('');
    $entity->allows('id')->andReturn(45);

    $obj = new HasDefaultEntityType();

    $this->assertFalse($obj->hasEntity());

    $obj->setEntity($entity);

    $this->assertTrue($obj->hasEntity());
    $this->assertSame($entity, $obj->getEntity());

    $this->assertSame('f8975298eb75f1fa94c322f6d52711be', $obj->getEntityCacheId());

    $this->assertTrue($obj->lorem());
  }

  public function testHasUserWorks() {
    $node = \Mockery::mock(AccountInterface::class);
    $node->allows('id')->andReturn(123);
    $node->allows('getEntityTypeId')->andReturn('user');

    $obj = new EntityManipulator();
    $this->assertFalse($obj->hasUser());
    $this->assertTrue($obj->setUser($node)->hasUser());
  }

  public function testHasNodeWorks() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');

    $obj = new EntityManipulator();
    $this->assertFalse($obj->hasNode());
    $this->assertTrue($obj->setNode($node)->hasNode());
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   * @expectedExceptionMessage of type node must be
   */
  public function testRequireNodeThrowsWhenNoNodeSet() {
    $obj = new EntityManipulator();
    $obj->doSomethingWithAnyNode();
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   * @expectedExceptionMessageRegExp  /of type node.+?one of bundles: page/
   */
  public function testRequirePageNodeEntityThrowsWithBlogNode() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $node->allows('id')->andReturn(444);
    $node->allows('bundle')->andReturn('blog');

    $obj = new EntityManipulator();
    $obj->setNode($node)->doSomethingWithPageNodeEntity();
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   * @expectedExceptionMessageRegExp  /of type node.+?one of bundles: page/
   */
  public function testRequirePageNodeThrowsWithBlogNode() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $node->allows('id')->andReturn(444);
    $node->allows('bundle')->andReturn('blog');

    $obj = new EntityManipulator();
    $obj->setNode($node)->doSomethingWithPageNode();
  }

  public function testRequirePageNodeEntityReturnsAsExpected() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $node->allows('id')->andReturn(444);
    $node->allows('bundle')->andReturn('page');

    $obj = new EntityManipulator();
    $return = $obj->setNode($node)->doSomethingWithPageNodeEntity();
    $this->assertSame('node', $return[0]);
    $this->assertSame($node, $return[1]);
    $this->assertSame('page', $return[2]);
    $this->assertSame(444, $return[3]);
  }

  public function testRequirePageNodeReturnsAsExpected() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $node->allows('id')->andReturn(444);
    $node->allows('bundle')->andReturn('page');

    $obj = new EntityManipulator();
    $return = $obj->setNode($node)->doSomethingWithPageNode();
    $this->assertSame($node, $return[0]);
    $this->assertSame('page', $return[1]);
    $this->assertSame(444, $return[2]);
  }

  public function testRequireNodeNoBundleRequirementReturnsAsExpected() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $node->allows('id')->andReturn(444);
    $node->allows('bundle')->andReturn('page');

    $obj = new EntityManipulator();
    $return = $obj->setNode($node)->doSomethingWithAnyNode();
    $this->assertSame($node, $return[0]);
    $this->assertSame('page', $return[1]);
    $this->assertSame(444, $return[2]);
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   * @expectedExceptionMessage of type user must be
   */
  public function testRequireUserThrowsWhenNoUserSet() {
    $obj = new EntityManipulator();
    $obj->doSomethingWithUser();
  }

  public function testRequireUserReturnsAsExpected() {
    $user = \Mockery::mock(UserInterface::class);
    $user->allows('getEntityTypeId')->andReturn('user');
    $user->allows('id')->andReturn(555);
    $user->allows('bundle')->andReturn('');

    $obj = new EntityManipulator();
    $return = $obj->setUser($user)->doSomethingWithUser();
    $this->assertSame($user, $return[0]);
    $this->assertSame(555, $return[1]);
  }

  public function testOnGetEntityCacheId() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $node->allows('id')->andReturn(183);
    $node->allows('bundle')->andReturn('page');

    $obj = new AnotherManipulaterWithCallbacks();
    $obj->setNode($node);
    for ($i = 0; $i < 30; ++$i) {
      $this->assertSame('9d6da3aeddd6b804b8ecd318ba65b5c0', $obj->getEntityCacheId('node'));
    }
  }

  public function testGetCacheIdReturnsUuid() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $node->allows('id')->andReturn(183);
    $node->allows('bundle')->andReturn('page');

    $user = \Mockery::mock(UserInterface::class);
    $user->allows('getEntityTypeId')->andReturn('user');
    $user->allows('id')->andReturn(184);
    $user->allows('bundle')->andReturn('');

    $obj = new EntityManipulator();
    $obj->setNode($node)->setUser($user);
    $this->assertSame('6eda6b0ea1e36bcbce4f1ce4f6a0993d', $obj->getEntityCacheId('user'));
    $this->assertSame('b06dc48a2702bc5c500eb12a57ec0e77', $obj->getEntityCacheId('node'));
  }

  public function testSetterCallbackWorks() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('getEntityTypeId')->andReturn('node');
    $user = \Mockery::mock(UserInterface::class);
    $user->allows('getEntityTypeId')->andReturn('user');

    $obj = new AnotherManipulaterWithCallbacks();
    $stack = $obj->setNode($node)->setUser($user)->stack;
    $this->assertCount(2, $stack);
    $this->assertSame('node', $stack[0][0]);
    $this->assertSame($node, $stack[0][1]);
    $this->assertSame('user', $stack[1][0]);
    $this->assertSame($user, $stack[1][1]);
  }

  public function testAssertSettersAndGettersWorkAsExpected() {
    $node = \Mockery::mock(NodeInterface::class);
    $node->allows('bundle')->andReturn('page');
    $node->allows('id')->andReturn(1);
    $node->allows('getEntityTypeId')->andReturn('node');

    $obj = new EntityManipulator();
    $obj->setNode($node);
    $this->assertSame($node, $obj->getNode());
    $this->assertSame($node, $obj->getEntity('node'));

    $node2 = \Mockery::mock(NodeInterface::class);
    $node2->allows('getEntityTypeId')->andReturn('node');
    $node2->allows('bundle')->andReturn('page');
    $node2->allows('id')->andReturn(3);

    $this->assertNotSame($node, $node2);

    // Assert adding another node, will replace the first.
    $obj->setEntity($node2);
    $this->assertSame($node2, $obj->getNode());
    $this->assertSame($node2, $obj->getEntity('node'));

    $user = \Mockery::mock(UserInterface::class);
    $user->allows('getEntityTypeId')->andReturn('user');
    $user->allows('bundle')->andReturn('');
    $user->allows('id')->andReturn(2);

    // Add a user entity.
    $obj->setUser($user);
    $this->assertSame($user, $obj->getUser());
    $this->assertSame($user, $obj->getEntity('user'));

    // Make sure $node2 is still around.
    $this->assertSame($node2, $obj->getNode());
  }

}

class OnSetSwap implements HasEntityInterface {

  use HasEntityTrait;

  private function onSetEntity() {
    global $new_entity_to_swap;

    return $new_entity_to_swap;
  }
}

class BadClass implements HasEntityInterface {

  use HasEntityTrait;

  private function onSetEntity($entity) {
    return 'bad_return';
  }
}

class HasDefaultEntityType implements HasEntityInterface {

  const ENTITY_TYPE_ID = 'image_style';

  use HasEntityTrait;

  public function lorem() {
    $this->requireEntity('image_style');

    return TRUE;
  }

  public function ipsum() {
    $this->requireEntity('');
  }
}

class EntityManipulator implements HasEntityInterface, HasNodeInterface, HasUserInterface {

  use HasEntityTrait;
  use HasNodeTrait;
  use HasUserTrait;

  public function doSomethingWithUser() {
    return $this->requireUser();
  }

  public function doSomethingWithAnyNode() {
    return $this->requireNode();
  }

  public function doSomethingWithPageNode() {
    return $this->requireNode(['page']);
  }

  public function doSomethingWithPageNodeEntity() {
    return $this->requireEntity('node', ['page']);
  }

}

class AnotherManipulaterWithCallbacks implements HasEntityInterface, HasNodeInterface, HasUserInterface {

  use HasEntityTrait;
  use HasNodeTrait;
  use HasUserTrait;

  public $stack = [];

  private function onSetEntity(EntityInterface $entity) {
    $this->stack[] = [$entity->getEntityTypeId(), $entity];
  }

  /**
   * In this test we are shuffling to make sure that config key order does not
   * change the cache id.
   *
   * @return array
   *   The cache config.
   */
  public function onGetEntityCacheId() {
    $config = ['lorem', 'ipsum', 'dolar', 'sit', 'amet', 'consectitur'];
    shuffle($config);
    $config = array_fill_keys($config, TRUE);

    return $config;
  }

}
