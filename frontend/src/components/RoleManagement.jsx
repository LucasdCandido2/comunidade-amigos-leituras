import React, { useState, useEffect } from 'react';
import api from '../services/api';
import { roleService } from '../services/roleService';

export function RoleManagement({ onBack, user }) {
  const [activeTab, setActiveTab] = useState('roles');
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [editingRole, setEditingRole] = useState(null);
  const [showCreate, setShowCreate] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    display_name: '',
    description: '',
    permission_ids: [],
  });
  const [assignRoleData, setAssignRoleData] = useState({
    user_id: '',
    role_id: '',
  });
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [rolesRes, permsRes, usersRes] = await Promise.all([
        roleService.getAll(),
        roleService.getPermissions(),
        api.get('/users'),
      ]);
      setRoles(rolesRes.data.data || rolesRes.data);
      setPermissions(permsRes.data);
      setUsers(usersRes.data.data || usersRes.data);
    } catch (err) {
      setError('Erro ao carregar dados');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccess(null);

    try {
      if (editingRole) {
        await api.put(`/roles/${editingRole.id}`, formData);
        setSuccess('Cargo atualizado com sucesso');
      } else {
        await api.post('/roles', formData);
        setSuccess('Cargo criado com sucesso');
      }
      setShowCreate(false);
      setEditingRole(null);
      setFormData({ name: '', display_name: '', description: '', permission_ids: [] });
      loadData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao salvar cargo');
    }
  };

  const handleEdit = (role) => {
    setEditingRole(role);
    setFormData({
      name: role.name,
      display_name: role.display_name,
      description: role.description || '',
      permission_ids: role.permissions?.map(p => p.id) || [],
    });
    setShowCreate(true);
  };

  const handleDelete = async (roleId) => {
    if (!confirm('Tem certeza que deseja deletar este cargo?')) return;

    try {
      await api.delete(`/roles/${roleId}`);
      setSuccess('Cargo deletado com sucesso');
      loadData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao deletar cargo');
    }
  };

  const handleAssignRole = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccess(null);

    if (!assignRoleData.user_id || !assignRoleData.role_id) {
      setError('Selecione um usuário e um cargo');
      return;
    }

    try {
      await api.post('/users/assign-role', assignRoleData);
      setSuccess('Cargo atribuído com sucesso');
      setAssignRoleData({ user_id: '', role_id: '' });
      loadData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao atribuir cargo');
    }
  };

  const togglePermission = (permId) => {
    setFormData(prev => ({
      ...prev,
      permission_ids: prev.permission_ids.includes(permId)
        ? prev.permission_ids.filter(id => id !== permId)
        : [...prev.permission_ids, permId]
    }));
  };

  const canManageRoles = user?.role?.name === 'owner';

  if (loading) {
    return (
      <div className="loading-state">
        <div className="spinner" />
        <span>Carregando...</span>
      </div>
    );
  }

  return (
    <div className="admin-panel">
      <div className="admin-panel__header">
        <button onClick={onBack} className="btn btn--ghost">
          ← Voltar
        </button>
        <h2>Painel de Administração</h2>
      </div>

      <div className="admin-panel__tabs">
        <button
          className={`admin-panel__tab ${activeTab === 'roles' ? 'admin-panel__tab--active' : ''}`}
          onClick={() => setActiveTab('roles')}
        >
          Cargos e Permissões
        </button>
        <button
          className={`admin-panel__tab ${activeTab === 'users' ? 'admin-panel__tab--active' : ''}`}
          onClick={() => setActiveTab('users')}
        >
          Membros
        </button>
        <button
          className={`admin-panel__tab ${activeTab === 'assign' ? 'admin-panel__tab--active' : ''}`}
          onClick={() => setActiveTab('assign')}
        >
          Atribuir Cargos
        </button>
      </div>

      {error && <div className="alert alert--error">{error}</div>}
      {success && <div className="alert alert--success">{success}</div>}

      {activeTab === 'roles' && (
        <div className="admin-panel__content">
          <div className="admin-panel__section-header">
            <h3>Gerenciar Cargos e Permissões</h3>
            {canManageRoles && (
              <button onClick={() => { setShowCreate(true); setEditingRole(null); setFormData({ name: '', display_name: '', description: '', permission_ids: [] }); }} className="btn btn--primary">
                + Novo Cargo
              </button>
            )}
          </div>

          {showCreate && (
            <div className="admin-panel__form">
              <h4>{editingRole ? 'Editar Cargo' : 'Novo Cargo'}</h4>
              <form onSubmit={handleSubmit}>
                <div className="form-group">
                  <label>Nome interno (slug)</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    disabled={editingRole}
                    placeholder="nome-do-cargo"
                    required
                  />
                </div>
                <div className="form-group">
                  <label>Nome de exibição</label>
                  <input
                    type="text"
                    value={formData.display_name}
                    onChange={(e) => setFormData({ ...formData, display_name: e.target.value })}
                    placeholder="Nome visível"
                    required
                  />
                </div>
                <div className="form-group">
                  <label>Descrição</label>
                  <textarea
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    placeholder="Descrição opcional"
                  />
                </div>

                <div className="form-group">
                  <label>Permissões</label>
                  <div className="admin-panel__permissions">
                    {permissions.map(perm => (
                      <label key={perm.id} className="checkbox-label">
                        <input
                          type="checkbox"
                          checked={formData.permission_ids.includes(perm.id)}
                          onChange={() => togglePermission(perm.id)}
                        />
                        <span>{perm.display_name}</span>
                      </label>
                    ))}
                  </div>
                </div>

                <div className="form-actions">
                  <button type="submit" className="btn btn--primary">
                    {editingRole ? 'Atualizar' : 'Criar'}
                  </button>
                  <button type="button" onClick={() => { setShowCreate(false); setEditingRole(null); }} className="btn btn--ghost">
                    Cancelar
                  </button>
                </div>
              </form>
            </div>
          )}

          <div className="admin-panel__list">
            {roles.map(role => (
              <div key={role.id} className="role-card">
                <div className="role-card__header">
                  <strong>{role.display_name}</strong>
                  <span className="role-card__name">({role.name})</span>
                </div>
                <p className="role-card__desc">{role.description}</p>
                <div className="role-card__permissions">
                  {role.permissions?.map(p => (
                    <span key={p.id} className="permission-badge">{p.display_name}</span>
                  ))}
                </div>
                {canManageRoles && role.name !== 'owner' && (
                  <div className="role-card__actions">
                    <button onClick={() => handleEdit(role)} className="btn btn--sm btn--ghost">
                      Editar
                    </button>
                    <button onClick={() => handleDelete(role.id)} className="btn btn--sm btn--danger">
                      Deletar
                    </button>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      )}

      {activeTab === 'users' && (
        <div className="admin-panel__content">
          <div className="admin-panel__section-header">
            <h3>Todos os Membros</h3>
          </div>

          <div className="admin-panel__users-table">
            <table>
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Cargo</th>
                  <th>Permissões</th>
                </tr>
              </thead>
              <tbody>
                {users.map(u => (
                  <tr key={u.id}>
                    <td>{u.name}</td>
                    <td>{u.email}</td>
                    <td>
                      <span className={`badge badge--${u.role?.name || 'member'}`}>
                        {u.role?.display_name || 'Sem cargo'}
                      </span>
                    </td>
                    <td>
                      <div className="user-permissions">
                        {u.role?.permissions?.slice(0, 3).map(p => (
                          <span key={p.id} className="permission-badge">{p.name}</span>
                        ))}
                        {u.role?.permissions?.length > 3 && (
                          <span className="permission-badge">+{u.role.permissions.length - 3}</span>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {activeTab === 'assign' && (
        <div className="admin-panel__content">
          <div className="admin-panel__section-header">
            <h3>Atribuir Cargo a Membro</h3>
          </div>

          {canManageRoles ? (
            <div className="admin-panel__form">
              <form onSubmit={handleAssignRole}>
                <div className="form-group">
                  <label>Selecionar Membro</label>
                  <select
                    value={assignRoleData.user_id}
                    onChange={(e) => setAssignRoleData({ ...assignRoleData, user_id: e.target.value })}
                    required
                  >
                    <option value="">Selecione...</option>
                    {users.map(u => (
                      <option key={u.id} value={u.id}>
                        {u.name} ({u.email})
                      </option>
                    ))}
                  </select>
                </div>
                <div className="form-group">
                  <label>Selecionar Cargo</label>
                  <select
                    value={assignRoleData.role_id}
                    onChange={(e) => setAssignRoleData({ ...assignRoleData, role_id: e.target.value })}
                    required
                  >
                    <option value="">Selecione...</option>
                    {roles.map(r => (
                      <option key={r.id} value={r.id}>
                        {r.display_name}
                      </option>
                    ))}
                  </select>
                </div>
                <button type="submit" className="btn btn--primary">
                  Atribuir Cargo
                </button>
              </form>
            </div>
          ) : (
            <div className="alert alert--warning">
              Apenas o Dono pode atribuir cargos aos membros.
            </div>
          )}
        </div>
      )}
    </div>
  );
}