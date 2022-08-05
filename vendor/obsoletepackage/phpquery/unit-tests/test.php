<?php
require_once 'PHPUnit/Autoload.php';
require_once '../phpQuery/phpQuery.php';
//phpQuery::$debug = true;

class phpQueryBasicTest extends PHPUnit_Framework_TestCase {
    function provider() {
        // TODO change filename
        return array( array(
                phpQuery::newDocumentFile('test.html')
        ));
    }

    /**
     * @param phpQueryObject $pq
     * @dataProvider provider
     * @return void
     */
    function testFilterWithPseudoclass( $pq ) {
//        xdebug_break();
//    function testFilterWithPseudoclass( $pq ) {
//        print_r(`ls`);
//        $pq = phpQuery::newDocumentFile('test.html');
        $pq = $pq->find('p')
            ->filter('.body:gt(1)');
        $result = array(
            'p.body',
        );

        $this->assertTrue( $pq->whois() == $result );
    }


    /**
     * @param phpQueryObject $pq
     * @dataProvider provider
     * @return void
     */
    function testSlice( $pq ) {
        $testResult = array(
            'li#testID',
        );
        $pq = $pq->find('li')
            ->slice(1, 2);
        
        $this->assertTrue( $pq->whois() == $testResult );
    }

    /**
     * @param phpQueryObject $pq
     * @dataProvider provider
     * @return void
     */
    function testSlice2( $pq ) {
        // SLICE2
        $testResult = array(
            'li#testID',
            'li',
            'li#i_have_nested_list',
            'li.nested',
        );

        $pq = $pq->find('li')
            ->slice(1, -1);
        
        $this->assertTrue( $pq->whois() == $testResult );
    }


    /**
     * @return void
     */
    function testMultiInsert() {
        // Multi-insert
        $pq = phpQuery::newDocument('<li><span class="field1"></span><span class="field1"></span></li>')
            ->find('.field1')
                ->php('longlongtest');
        $validResult = '<li><span class="field1"><php>longlongtest</php></span><span class="field1"><php>longlongtest</php></span></li>';
        similar_text($pq->htmlOuter(), $validResult, $similarity);

        $this->assertGreaterThan( 80, $similarity);

    }

    /**
     * @param phpQueryObject $pq
     * @dataProvider provider
     * @return void
     */
    function testIndex( $pq ) {
        $testResult = 1;
        $pq = $pq->find('p')
            ->index(
                $pq->find('p.title:first')
            );

        $this->assertTrue( $pq == $testResult );
    }

    /**
     * @param phpQueryObject $pq
     * @dataProvider provider
     * @return void
     */
    function testClone( $pq ) {
        $testResult = 3;
        $document = null;
        $pq = $pq->toReference($document)
            ->find('p:first');
        
        foreach(array(0,1,2) as $i) {
            $pq->clone()
                ->addClass("clone-test")
                ->addClass("class-$i")
                ->insertBefore($pq);
        }

        $size = $document->find('.clone-test')->size();
        $this->assertEquals( $testResult, $size);
    }


    /**
     * @param phpQueryObject $pq
     * @dataProvider provider
     * @return void
     */
    function testNextSibling( $pq ) {
        $testResult = 3;
        $document = null;
        $result = $pq->find('li:first')
            ->next()
            ->next()
            ->prev()
            ->is('#testID');

        $this->assertTrue( $result );
    }

    /**
     * @param phpQueryObject $pq
     * @dataProvider provider
     * @return void
     */
    function testSimpleDataInsertion( $pq ) {
        $testName = 'Simple data insertion';
        $testResult = <<<EOF
<div class="articles">
        div.articles text node
        <ul>

        <li>
                <p>This is paragraph of first LI</p>
                <p class="title">News 1 title</p>
                <p class="body">News 1 body</p>
            </li>

<li>
                <p>This is paragraph of first LI</p>
                <p class="title">News 2 title</p>
                <p class="body">News 2 body</p>
            </li>
<li>
                <p>This is paragraph of first LI</p>
                <p class="title">News 3</p>
                <p class="body">News 3 body</p>
            </li>
</ul>
<p>paragraph after UL</p>
    </div>
EOF;
        $rows = array(
            array(
                'title' => 'News 1 title',
                'body' => 'News 1 body',
            ),
            array(
                'title' => 'News 2 title',
                'body' => 'News 2 body',
            ),
            array(
                'title' => 'News 3',
                'body' => 'News 3 body',
            ),
        );
        $articles = $pq->find('.articles ul');
        $rowSrc = $articles->find('li')
                ->remove()
                ->eq(0);
        foreach ($rows as $r) {
            $row = $rowSrc->_clone();
            foreach ($r as $field => $value) {
                $row->find(".{$field}")
                        ->html($value);
                //		die($row->htmlOuter());
            }
            $row->appendTo($articles);
        }
        $result = $pq->find('.articles')->htmlOuter();
        //print htmlspecialchars("<pre>{$result}</pre>").'<br />';
        $similarity = 0.0;
        similar_text($testResult, $result, $similarity);

        $this->assertGreaterThan( 90, $similarity);
    }


//    function __construct() {
//        xdebug_break();
//        parent::__construct();
//    }
}

$test = new phpQueryBasicTest();
//$test->testFilterWithPseudoclass();
$result = null;
//$test->run($result);