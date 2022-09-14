const App = () => {
    const deleteBtns = document.getElementsByClassName('wpcs-mark-role-delete');

    for (let deleteBtn of deleteBtns) {
        deleteBtn.addEventListener('click', toggleRoleToDelete);
    }
}

function toggleRoleToDelete(event) {
    const role = event.target.dataset['targetRole'];
    const rolesForm = document.getElementById('manage_plugins_roles');
    const currentStatus = parseInt(rolesForm.elements[`delete_roles[${role}]`].value);
    const roleContainer = document.getElementById(`wpcs-role-${role}`);

    if (!currentStatus) {
        rolesForm.elements[`delete_roles[${role}]`].value = '1';
        event.target.innerText = 'Keep Role';
        roleContainer.classList.add('mark-delete');
    } else {
        rolesForm.elements[`delete_roles[${role}]`].value = '0';
        event.target.innerText = 'Mark Delete Role';
        roleContainer.classList.remove('mark-delete');
    }
}

document.addEventListener("DOMContentLoaded", App);
