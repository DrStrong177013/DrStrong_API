<div class="sidebar">
    <ul class="sidebar-menu">
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">Upload Test Cases</a></li>
        <li><a href="#">Test Results</a></li>
        <li><a href="#">Settings</a></li>
    </ul>
</div>

<style>
    .sidebar {
        width: 200px;
        height: 100vh;
        background-color: #343a40;
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-menu li {
        margin-bottom: 20px;
    }

    .sidebar-menu li a {
        color: white;
        text-decoration: none;
        font-size: 18px;
        display: block;
        padding: 10px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .sidebar-menu li a:hover {
        background-color: #495057;
    }
</style>
