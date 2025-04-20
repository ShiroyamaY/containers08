<?php

require_once __DIR__ . '/testframework.php';

$configPath = __DIR__ . '/../site/config.php';
$config = [];
require_once $configPath;

require_once __DIR__ . '/../site/modules/database.php';
require_once __DIR__ . '/../site/modules/page.php';

$tests = new TestFramework();

function testDbConnection() {
    global $config;
    
    info("Testing database connection");
    
    try {
        $db = new Database($config["db"]["path"]);
        return assertExpression(true, "Database connection successful", "Failed to connect to database");
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testDbCount() {
    global $config;
    
    info("Testing Count method");
    
    try {
        $db = new Database($config["db"]["path"]);
        $count = $db->Count("page");
        
        return assertExpression(
            $count >= 3, 
            "Count method returned {$count} rows as expected", 
            "Count method failed, expected >= 3 rows, got {$count}"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testDbCreate() {
    global $config;
    
    info("Testing Create method");
    
    try {
        $db = new Database($config["db"]["path"]);
        $data = [
            'title' => 'Test Page',
            'content' => 'Test Content ' . time() 
        ];
        
        $id = $db->Create("page", $data);
        
        return assertExpression(
            $id > 0, 
            "Create method returned ID {$id}", 
            "Create method failed to return a valid ID"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testDbRead() {
    global $config;
    
    info("Testing Read method");
    
    try {
        $db = new Database($config["db"]["path"]);
        
        $data = $db->Read("page", 1);
        
        return assertExpression(
            isset($data['id']) && $data['id'] == 1, 
            "Read method successfully retrieved record with ID 1", 
            "Read method failed to retrieve record with ID 1"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testDbUpdate() {
    global $config;
    
    info("Testing Update method");
    
    try {
        $db = new Database($config["db"]["path"]);
        
        $data = [
            'title' => 'Update Test',
            'content' => 'Original Content'
        ];
        
        $id = $db->Create("page", $data);
        
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ];
        
        $result = $db->Update("page", $id, $updateData);
        
        $updatedRecord = $db->Read("page", $id);
        
        return assertExpression(
            $result && $updatedRecord['title'] == 'Updated Title', 
            "Update method successfully modified record with ID {$id}", 
            "Update method failed to modify record with ID {$id}"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testDbDelete() {
    global $config;
    
    info("Testing Delete method");
    
    try {
        $db = new Database($config["db"]["path"]);
        
        $data = [
            'title' => 'Delete Test',
            'content' => 'Content to delete'
        ];
        
        $id = $db->Create("page", $data);
        
        $result = $db->Delete("page", $id);
        
        $deletedRecord = $db->Read("page", $id);
        
        return assertExpression(
            $result && $deletedRecord === null, 
            "Delete method successfully removed record with ID {$id}", 
            "Delete method failed to remove record with ID {$id}"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testDbFetch() {
    global $config;
    
    info("Testing Fetch method");
    
    try {
        $db = new Database($config["db"]["path"]);
        
        $results = $db->Fetch("SELECT * FROM page LIMIT 3");
        
        return assertExpression(
            is_array($results) && count($results) > 0, 
            "Fetch method returned " . count($results) . " rows", 
            "Fetch method failed to return results"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testDbExecute() {
    global $config;
    
    info("Testing Execute method");
    
    try {
        $db = new Database($config["db"]["path"]);

        $result = $db->Execute("UPDATE page SET content = 'Updated via Execute' WHERE id = 1");
        
        return assertExpression(
            $result !== false, 
            "Execute method successfully ran query", 
            "Execute method failed to run query"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

function testPageRender() {
    info("Testing Page Render method");
    
    try {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_tpl_');
        file_put_contents($tempFile, 'Title: {{title}}, Content: {{content}}');
        
        $page = new Page($tempFile);
        
        $data = [
            'title' => 'Test Title',
            'content' => 'Test Content'
        ];
        
        $rendered = $page->Render($data);
        $expected = 'Title: Test Title, Content: Test Content';
        
        unlink($tempFile);
        
        return assertExpression(
            $rendered === $expected, 
            "Page Render method correctly replaced placeholders", 
            "Page Render method failed to replace placeholders correctly"
        );
    } catch (Exception $e) {
        error("Exception: " . $e->getMessage());
        return false;
    }
}

$tests->add('Database connection', 'testDbConnection');
$tests->add('Table count', 'testDbCount');
$tests->add('Data create', 'testDbCreate');
$tests->add('Data read', 'testDbRead');
$tests->add('Data update', 'testDbUpdate');
$tests->add('Data delete', 'testDbDelete');
$tests->add('Data fetch', 'testDbFetch');
$tests->add('SQL execute', 'testDbExecute');
$tests->add('Page render', 'testPageRender');

$tests->run();

echo "Tests completed: " . $tests->getResult();