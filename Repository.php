<?php
require_once 'Connection.php';
require_once 'Employee.php';
require_once 'Task.php';

class Repository {
    protected $pdo;
    public function __construct() {
        $this->pdo = getConnection(); // Needs to be configured!
    }
    function createEmployeesTable(): void
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS employees (
            id INTEGER AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            picture VARCHAR(255) NOT NULL
        )");

        $stmt->execute();
    }

    function getAllEmployees() : array
    {
        $stmt = $this->pdo->prepare('SELECT id, first_name, last_name, picture FROM employees');
        $stmt->execute();

        $employees = [];
        foreach ($stmt as $row) {
            $id = $row['id'];
            $firstName = htmlspecialchars_decode($row['first_name']);
            $lastName = htmlspecialchars_decode($row['last_name']);
            $picture = $row['picture'];
            $employee = new Employee($id, $firstName, $lastName, $picture);
            $employees[] = $employee;
        }

        return $employees;
    }

    function getEmployeeById(string $id): ?Employee
    {
        $employees = $this->getAllEmployees();
        foreach ($employees as $employee) {
            if ($employee->id === $id) {
                return $employee;
            }
        }
        return null;
    }

    function saveEmployee(Employee $employee): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO employees (first_name, last_name, picture) VALUES (:firstName, :lastName, :picture) ');
        $stmt->bindValue(':firstName', htmlspecialchars($employee->firstName));
        $stmt->bindValue(':lastName', htmlspecialchars($employee->lastName));
        $stmt->bindValue(':picture', $employee->picture);
        $stmt->execute();
    }

    function editEmployee(Employee $employee): void
    {
        $stmt = $this->pdo->prepare('UPDATE employees SET first_name = :firstName, last_name = :lastName, picture = :picture WHERE id = :id');
        $stmt->bindValue(':firstName', htmlspecialchars($employee->firstName));
        $stmt->bindValue(':lastName', htmlspecialchars($employee->lastName));
        $stmt->bindValue(':picture', $employee->picture);
        $stmt->bindValue(':id', $employee->id);
        $stmt->execute();
    }

    function deleteEmployee(Employee $employee): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM employees WHERE id = :id');
        $stmt->bindValue(':id', $employee->id);
        $stmt->execute();
    }


    function getEmployeesTaskCount(): array
    {
        $stmt = $this->pdo->prepare('SELECT employees.id, COUNT(tasks.id) AS task_count FROM employees 
        LEFT JOIN tasks ON tasks.assigned_person_id = employees.id GROUP BY employees.id');

        $stmt->execute();

        $result = [];
        foreach ($stmt as $row) {
            $result[$row['id']] = $row['task_count'];
        }

        return $result;
    }

    function validateEmployee(Employee $employee): array
    {
        $errors = [];
        if (strlen($employee->firstName) < 1 || strlen($employee->firstName) > 21) {
            $errors[] = 'First name must be between 1 and 21 characters.';
        }
        if (strlen($employee->lastName) < 2 || strlen($employee->lastName) > 22) {
            $errors[] = 'Last name must be between 2 and 22 characters.';
        }
        return $errors;
    }


    function createTasksTable(): void
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER AUTO_INCREMENT PRIMARY KEY,
            description TEXT NOT NULL,
            estimate INTEGER NOT NULL,
            assigned_person_id INTEGER,
            status VARCHAR(12) NOT NULL
        )");

        $stmt->execute();
    }

    function getAllTasks() : array
    {
        $stmt = $this->pdo->prepare('SELECT id, description, estimate, assigned_person_id, status FROM tasks');
        $stmt->execute();

        $tasks = [];
        foreach ($stmt as $row) {
            $id = $row['id'];
            $description = htmlspecialchars_decode($row['description']);
            $estimate = $row['estimate'];
            $employeeId = $row['assigned_person_id'];
            $status = $row['status'];
            $task = new Task($id, $description, $estimate, $employeeId, $status);
            $tasks[] = $task;
        }

        return $tasks;
    }

    function getTaskById(string $id): ?Task
    {
        $tasks = $this->getAllTasks();
        foreach ($tasks as $task) {
            if ($task->id === $id) {
                return $task;
            }
        }
        return null;
    }

    function saveTask(Task $task): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO tasks (description, estimate, assigned_person_id, status) VALUES (:description, :estimate, :employee_id, :status) ');
        $stmt->bindValue(':description', htmlspecialchars($task->description));
        $stmt->bindValue(':estimate', $task->estimate);
        $stmt->bindValue(':employee_id', $task->employeeId);
        $stmt->bindValue(':status', $task->status);
        $stmt->execute();
    }

    function editTask(Task $task): void
    {
        $stmt = $this->pdo->prepare('UPDATE tasks SET description = :description, estimate = :estimate, assigned_person_id = :employeeId, status = :status WHERE id = :id');
        $stmt->bindValue(':description', htmlspecialchars($task->description));
        $stmt->bindValue(':estimate', $task->estimate);
        $stmt->bindValue(':employeeId', $task->employeeId);
        $stmt->bindValue(':status', $task->status);
        $stmt->bindValue(':id', $task->id);
        $stmt->execute();
    }

    function deleteTask(Task $task): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->bindValue(':id', $task->id);
        $stmt->execute();
    }

    function validateTask(Task $task): array
    {
        $errors = [];
        if (strlen($task->description) <= 4 || strlen($task->description) >= 41) {
            $errors[] = 'Description must be 5 to 40 characters!';
        }
        return $errors;
    }

}
