import React, { useState, useEffect, useMemo } from 'react';
import api from '../services/api';
import { roleService } from '../services/roleService';
import { registrationRequestService } from '../services/registrationRequestService';

const PERMISSION_GROUPS = {
  'Usuários': ['view_users', 'create_user', 'edit_user', 'delete_user', 'invite_user'],
  'Tópicos': ['view_topics', 'create_topic', 'edit_own_topic', 'edit_any_topic', 'delete_own_topic', 'delete_any_topic', 'pin_topic', 'lock_topic'],
  'Comentários': ['create_interaction', 'edit_own_interaction', 'edit_any_interaction', 'delete_own_interaction', 'delete_any_interaction'],
  'Biblioteca': ['view_works', 'suggest_work', 'approve_work', 'edit_work', 'delete_work'],
  'Arquivos': ['upload_asset', 'download_asset', 'delete_own_asset', 'delete_any_asset'],
  'Moderação': ['moderate_content', 'ban_user', 'shadow_ban_user'],
  'Sistema': ['view_settings', 'edit_settings', 'manage_roles', 'view_logs'],
  'Relatórios': ['view_reports', 'export_data'],
};

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
  const [registrationRequests, setRegistrationRequests] = useState([]);
  const [approveData, setApproveData] = useState({
    requestId: null,
    role_id: '',
  });
  const [showApproveModal, setShowApproveModal] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [rolesRes, permsRes, usersRes, requestsRes] = await Promise.all([
        roleService.getAll(),
        roleService.getPermissions(),
        api.get('/users'),
        canManageRoles ? registrationRequestService.getAll() : Promise.resolve({ data: [] }),
      ]);
      setRoles(rolesRes.data.data || rolesRes.data);
      setPermissions(permsRes.data);
      setUsers(usersRes.data.data || usersRes.data);
      setRegistrationRequests(requestsRes.data.data || requestsRes.data);
    } catch (err) {
      setError('Erro ao carregar dados');
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (requestId) => {
    try {
      await registrationRequestService.approve(requestId, approveData.role_id || null);
      setSuccess('Usuário aprovado com sucesso');
      setShowApproveModal(false);
      setApproveData({ requestId: null, role_id: '' });
      loadData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao aprovar usuário');
    }
  };

  const handleReject = async (requestId) => {
    if (!confirm('Tem certeza que deseja rejeitar esta solicitação?')) return;
    
    try {
      await registrationRequestService.reject(requestId);
      setSuccess('Solicitação rejeitada');
      loadData();
    } catch (err) {
      setError(err.response?.data?.message || 'Erro ao rejeitar solicitação');
    }
  };

  const openApproveModal = (requestId) => {
    setApproveData({ requestId, role_id: '' });
    setShowApproveModal(true);
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

  const toggleGroup = (groupName, groupPerms) => {
    const groupPermIds = groupPerms.map(name => {
      const perm = permissions.find(p => p.name === name);
      return perm?.id;
    }).filter(Boolean);

    const allSelected = groupPermIds.every(id => formData.permission_ids.includes(id));
    
    setFormData(prev => {
      if (allSelected) {
        return { ...prev, permission_ids: prev.permission_ids.filter(id => !groupPermIds.includes(id)) };
      } else {
        return { ...prev, permission_ids: [...new Set([...prev.permission_ids, ...groupPermIds])] };
      }
    });
  };

  const permissionsByGroup = useMemo(() => {
    const grouped = {};
    permissions.forEach(perm => {
      for (const [groupName, permNames] of Object.entries(PERMISSION_GROUPS)) {
        if (permNames.includes(perm.name)) {
          if (!grouped[groupName]) grouped[groupName] = [];
          grouped[groupName].push(perm);
          break;
        }
      }
    });
    return grouped;
  }, [permissions]);

  const getRolePermissionsSummary = (rolePerms) => {
    const summary = [];
    for (const [groupName, groupPerms] of Object.entries(permissionsByGroup)) {
      const rolePermIds = rolePerms?.map(p => p.id) || [];
      const matchingCount = groupPerms.filter(p => rolePermIds.includes(p.id)).length;
      if (matchingCount > 0) {
        summary.push(`${groupName} (${matchingCount})`);
      }
    }
    return summary;
  };

  const canManageRoles = user?.roles?.[0]?.name === 'owner';

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
        {canManageRoles && (
          <button
            className={`admin-panel__tab ${activeTab === 'requests' ? 'admin-panel__tab--active' : ''}`}
            onClick={() => setActiveTab('requests')}
          >
            Solicitações {registrationRequests.filter(r => r.status === 'pending').length > 0 && `(${registrationRequests.filter(r => r.status === 'pending').length})`}
          </button>
        )}
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
            <div className="admin-panel__form admin-panel__form--wide">
              <h4>{editingRole ? 'Editar Cargo' : 'Novo Cargo'}</h4>
              <form onSubmit={handleSubmit}>
                <div className="form-row">
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
                </div>
                <div className="form-group">
                  <label>Descrição</label>
                  <textarea
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    placeholder="Descrição opcional"
                    rows={2}
                  />
                </div>

                <div className="form-group">
                  <label>Permissões por Categoria</label>
                  <div className="permission-groups">
                    {Object.entries(permissionsByGroup).map(([groupName, groupPerms]) => {
                      const groupPermIds = groupPerms.map(p => p.id);
                      const allSelected = groupPermIds.every(id => formData.permission_ids.includes(id));
                      const someSelected = groupPermIds.some(id => formData.permission_ids.includes(id));

                      return (
                        <div key={groupName} className="permission-group">
                          <div className="permission-group__header">
                            <label className="checkbox-label checkbox-label--bold">
                              <input
                                type="checkbox"
                                checked={allSelected}
                                ref={(el) => { if (el) el.indeterminate = someSelected && !allSelected; }}
                                onChange={() => toggleGroup(groupName, PERMISSION_GROUPS[groupName])}
                              />
                              <span>{groupName}</span>
                            </label>
                            <span className="permission-group__count">
                              {groupPermIds.filter(id => formData.permission_ids.includes(id)).length}/{groupPerms.length}
                            </span>
                          </div>
                          <div className="permission-group__items">
                            {groupPerms.map(perm => (
                              <label key={perm.id} className="checkbox-label checkbox-label--inline">
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
                      );
                    })}
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

          <div className="admin-panel__roles-table">
            <table>
              <thead>
                <tr>
                  <th>Cargo</th>
                  <th>Descrição</th>
                  <th>Permissões</th>
                  {canManageRoles && <th>Ações</th>}
                </tr>
              </thead>
              <tbody>
                {roles.map(role => (
                  <tr key={role.id}>
                    <td>
                      <strong>{role.display_name}</strong>
                      <span className="role-card__name">({role.name})</span>
                    </td>
                    <td>{role.description || '-'}</td>
                    <td>
                      <div className="role-permissions-summary">
                        {getRolePermissionsSummary(role.permissions).map((summary, idx) => (
                          <span key={idx} className="permission-summary-item">{summary}</span>
                        ))}
                      </div>
                    </td>
                    {canManageRoles && (
                      <td>
                        {role.name !== 'owner' && (
                          <div className="role-card__actions">
                            <button onClick={() => handleEdit(role)} className="btn btn--sm btn--ghost">
                              Editar
                            </button>
                            <button onClick={() => handleDelete(role.id)} className="btn btn--sm btn--danger">
                              Deletar
                            </button>
                          </div>
                        )}
                      </td>
                    )}
                  </tr>
                ))}
              </tbody>
            </table>
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
                      <span className={`badge badge--${u.roles?.[0]?.name || 'member'}`}>
                        {u.roles?.[0]?.display_name || 'Sem cargo'}
                      </span>
                    </td>
                    <td>
                      <div className="user-permissions">
                        {u.roles?.[0]?.permissions?.slice(0, 3).map(p => (
                          <span key={p.id} className="permission-badge">{p.name}</span>
                        ))}
                        {u.roles?.[0]?.permissions?.length > 3 && (
                          <span className="permission-badge">+{u.roles[0].permissions.length - 3}</span>
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

      {activeTab === 'requests' && canManageRoles && (
        <div className="admin-panel__content">
          <div className="admin-panel__section-header">
            <h3>Solicitações de Cadastro</h3>
          </div>

          {registrationRequests.length === 0 ? (
            <div className="empty-state">
              <p>Nenhuma solicitação pendente</p>
            </div>
          ) : (
            <div className="admin-panel__requests-table">
              <table>
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Data da Solicitação</th>
                    <th>Status</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {registrationRequests.map(req => (
                    <tr key={req.id}>
                      <td>{req.name}</td>
                      <td>{req.email}</td>
                      <td>{new Date(req.created_at).toLocaleDateString('pt-BR')}</td>
                      <td>
                        <span className={`badge badge--${req.status === 'pending' ? 'warning' : req.status === 'approved' ? 'success' : 'danger'}`}>
                          {req.status === 'pending' ? 'Pendente' : req.status === 'approved' ? 'Aprovado' : 'Rejeitado'}
                        </span>
                      </td>
                      <td>
                        {req.status === 'pending' && (
                          <div className="request-actions">
                            <button onClick={() => openApproveModal(req.id)} className="btn btn--sm btn--primary">
                              Aprovar
                            </button>
                            <button onClick={() => handleReject(req.id)} className="btn btn--sm btn--danger">
                              Rejeitar
                            </button>
                          </div>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}

      {showApproveModal && (
        <div className="modal-overlay">
          <div className="modal">
            <h4>Aprovar Solicitação</h4>
            <p>Selecione o cargo que será atribuído ao novo usuário:</p>
            <div className="form-group">
              <select
                value={approveData.role_id}
                onChange={(e) => setApproveData({ ...approveData, role_id: e.target.value })}
              >
                <option value="">Sem cargo (acesso básico)</option>
                {roles.map(r => (
                  <option key={r.id} value={r.id}>
                    {r.display_name}
                  </option>
                ))}
              </select>
            </div>
            <div className="modal__actions">
              <button onClick={() => handleApprove(approveData.requestId)} className="btn btn--primary">
                Confirmar
              </button>
              <button onClick={() => setShowApproveModal(false)} className="btn btn--ghost">
                Cancelar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}