import React, { useState, useEffect } from 'react';
import api from '../services/api';

export function RoleManagement({ onBack, user }) {
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [editingRole, setEditingRole] = useState(null);
  const [showCreate, setShowCreate] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    display_name: '',
    description: '',
    permission_ids: [],
  });
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [rolesRes, permsRes] = await Promise.all([
        api.get('/roles'),
        api.get('/permissions'),
      ]);
      setRoles(rolesRes.data);
      setPermissions(permsRes.data);
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

  const togglePermission = (permId) => {
    setFormData(prev => ({
      ...prev,
      permission_ids: prev.permission_ids.includes(permId)
        ? prev.permission_ids.filter(id => id !== permId)
        : [...prev.permission_ids, permId]
    }));
  };

  if (loading) {
    return (
      <div className="loading-state">
        <div className="spinner" />
        <span>Carregando...</span>
      </div>
    );
  }

  return (
    <div className="role-management">
      <div className="role-management__header">
        <button onClick={onBack} className="btn btn--ghost">
          ← Voltar
        </button>
        <h2>Gerenciamento de Cargos</h2>
        <button onClick={() => { setShowCreate(true); setEditingRole(null); setFormData({ name: '', display_name: '', description: '', permission_ids: [] }); }} className="btn btn--primary">
          + Novo Cargo
        </button>
      </div>

      {error && <div className="alert alert--error">{error}</div>}
      {success && <div className="alert alert--success">{success}</div>}

      {showCreate && (
        <div className="role-management__form">
          <h3>{editingRole ? 'Editar Cargo' : 'Novo Cargo'}</h3>
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
              <div className="role-management__permissions">
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

      <div className="role-management__list">
        <h3>Cargos existentes</h3>
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
          </div>
        ))}
      </div>
    </div>
  );
}