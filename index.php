<?php
require 'vendor/autoload.php';
require_once 'Employee.php';
require_once 'Task.php';
require_once 'Repository.php';

$command = $_GET['cmd'] ?? 'dashboard';
$message = $_GET['message'] ?? '';
$idGet = $_GET['id'] ?? 0;
$idPost = $_POST['id'] ?? 0;
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$description = $_POST['description'] ?? '';
$estimate = $_POST['estimate'] ?? 3;
$employeeId = $_POST['employeeId'] ?? 0;
$status = $_POST['isCompleted'] ?? '';

$repository = new Repository();
$latte = new Latte\Engine;


if ($command == 'dashboard') {
    $repository->createEmployeesTable(); // create table if it doesn't exist
    $repository->createTasksTable(); // create table if it doesn't exist

    $employees = $repository->getAllEmployees();
    $employeesTaskCount = $repository->getEmployeesTaskCount();
    $tasks = $repository->getAllTasks();
    $data = [
        'employees' => $employees,
        'employeesTaskCount' => $employeesTaskCount,
        'tasks' => $tasks
    ];

    $latte->render('dashboard.latte', $data);
    exit();
}

else if ($command == 'employee-form') {
    displayEmployeeForm($repository, $latte, $idPost, $command);
}

else if ($command == 'employee-edit') {
    displayEmployeeForm($repository, $latte, $idGet, $command);
}

else if ($command == 'employee-save') {
    $picture = 'missing.png';
    if (isset($_FILES['picture'])) {
        $uploadedFile = basename($_FILES['picture']['name']);
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $uploadedFile)) {
            $picture = $uploadedFile;
        }
    }
    $employee = new Employee(null, $firstName, $lastName, $picture);
    $errors = $repository->validateEmployee($employee);
    if ($errors) {
        $data = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'id' => $idPost,
            'errors' => $errors,
            'edit' => false
        ];

        $latte->render('employee-form.latte', $data);
        exit();
    }

    if (!empty($idPost)) {
        $employee = $repository->getEmployeeById($idPost);
        $employee->firstName = $firstName;
        $employee->lastName = $lastName;
        $employee->picture = $picture;
        $repository->editEmployee($employee);
    }
    else {
        $repository->saveEmployee($employee);
    }

    header('Location: index.php?cmd=employee-list&message=success');
    exit();
}

else if ($command == 'employee-list') {
    $employees = $repository->getAllEmployees();
    $data = [
        'message' => $message,
        'employees' => $employees
    ];

    $latte->render('employee-list.latte', $data);
    exit();
}

else if ($command == 'employee-delete') {
    $employee = $repository->getEmployeeById($idPost);
    $repository->deleteEmployee($employee);

    header('Location: index.php?cmd=employee-list&message=deleted');
    exit();
}


else if ($command == 'task-form') {
    displayTaskForm($repository, $latte, $idPost, $command);
}

else if ($command == 'task-edit') {
    displayTaskForm($repository, $latte, $idGet, $command);
}

else if ($command == 'task-save') {

    if ($employeeId === '' || !is_numeric($employeeId)) { // needed for not allowing sql injection
        $employeeId = 0;
    }

    if ($estimate === '' || !is_numeric($estimate)) {
        $estimate = 3;
    }

    if ($employeeId === 0) {
        $status = 'Open';
    } else {
        $status = 'Pending';
    }

    if (isset($_POST['isCompleted'])) {
        $status = 'Closed';
    }

    $task = new Task(null, $description, $estimate, $employeeId, $status);
    $errors = $repository->validateTask($task);
    $employees = $repository->getAllEmployees();

    if ($errors) {
        $selectedEmployee = $repository->getEmployeeById($employeeId);
        $data = [
            'errors' => $errors,
            'id' => $idPost,
            'description' => $description,
            'estimate' => $estimate,
            'selectedEmployee' => $selectedEmployee,
            'employees' => $employees,
            'status' => $status,
            'edit' => false

        ];

        $latte->render('task-form.latte', $data);
        exit();
    }

    if (!empty($idPost)) {
        $task = $repository->getTaskById($idPost);
        $task->description = $description;
        $task->estimate = $estimate;
        $task->employeeId = $employeeId;
        $task->status = $status;
        $repository->editTask($task);
    } else {
        $repository->saveTask($task);
    }

    header('Location: index.php?cmd=task-list&message=success');
    exit();
}

else if ($command == 'task-list') {
    $tasks = $repository->getAllTasks();

    $data = [
        'tasks' => $tasks,
        'message' => $message,
    ];

    $latte->render('task-list.latte', $data);
    exit();
}

else if ($command == 'task-delete') {
    $task = $repository->getTaskById($idPost);
    $repository->deleteTask($task);

    header('Location: index.php?cmd=task-list&message=deleted');
    exit();
}

function displayEmployeeForm($repository, $latte, $id, $command): void
{
    $employee = $repository->getEmployeeById($id);
    $firstName = $employee->firstName ?? '';
    $lastName = $employee->lastName ?? '';

    $data = [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'id' => $id,
        'errors' => [],
        'edit' => ($command === 'employee-edit')
    ];

    $latte->render('employee-form.latte', $data);
    exit();
}

function displayTaskForm($repository, $latte, $id, $command): void
{
    $task = $repository->getTaskById($id);
    $description = $task->description ?? '';
    $estimate = $task->estimate ?? 3;
    $employeeId = $task->employeeId ?? 0;
    $status = $task->status ?? 'Open';

    $selectedEmployee = $repository->getEmployeeById($employeeId);
    $employees = $repository->getAllEmployees();

    $data = [
        'errors' => [],
        'id' => $id,
        'description' => $description,
        'estimate' => $estimate,
        'selectedEmployee' => $selectedEmployee,
        'employees' => $employees,
        'status' => $status,
        'edit' => ($command === 'task-edit')
    ];

    $latte->render('task-form.latte', $data);
    exit();
}