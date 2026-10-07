/**
 * main.js - Sistema de Control Vehicular SECOTED
 * Lógica de interfaz de usuario y validaciones dinámicas
 */

document.addEventListener('DOMContentLoaded', () => {
    // Manejo dinámico de motivos en solicitudes (mostrar especificación si es requerido)
    const selectMotivo = document.getElementById('id_motivo');
    const wrapEspecificar = document.getElementById('wrap_especificar_motivo');

    if (selectMotivo && wrapEspecificar) {
        selectMotivo.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const requiereDetalle = selectedOption.getAttribute('data-requiere-detalle') === '1';

            if (requiereDetalle) {
                wrapEspecificar.style.display = 'block';
                const inputEspecificar = wrapEspecificar.querySelector('input, textarea');
                if (inputEspecificar) inputEspecificar.focus();
            } else {
                wrapEspecificar.style.display = 'none';
            }
        });
    }

    // Botón manual de cerrar alertas (data-bs-dismiss="alert")
    document.querySelectorAll('[data-bs-dismiss="alert"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const alertBox = btn.closest('.alert');
            if (alertBox) {
                alertBox.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                alertBox.style.opacity = '0';
                alertBox.style.transform = 'translateY(-6px)';
                setTimeout(() => alertBox.remove(), 250);
            }
        });
    });

    // Auto-ocultar alertas después de 6 segundos
    const alertas = document.querySelectorAll('.login-success, .login-error, .alert');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alerta.style.opacity = '0';
            alerta.style.transform = 'translateY(-6px)';
            setTimeout(() => alerta.remove(), 550);
        }, 6000);
    });

    // ─── Funcionalidad de Modales del Sistema ──────────────────
    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('active', 'show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('active', 'show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    // Botones para abrir modal (Bootstrap data-bs-toggle="modal")
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetSelector = btn.getAttribute('data-bs-target');
            if (targetSelector) {
                const targetModal = document.querySelector(targetSelector);
                if (targetModal) openModal(targetModal);
            }
        });
    });

    // Botones para cerrar modal (icono X o botón Cancelar)
    document.querySelectorAll('[data-close-modal], [data-bs-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = btn.closest('.modal-overlay, .modal');
            if (modal) closeModal(modal);
        });
    });
    // Cerrar al presionar la tecla ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active, .modal.active, .modal.show');
            if (activeModal) closeModal(activeModal);
        }
    });

    // ─── Módulo de Vehículos: Editar ───────────────────────────
    const modalEditar = document.getElementById('modalEditarVehiculo');
    if (modalEditar) {
        const inpId = document.getElementById('edit_id_vehiculo');
        const inpEconomico = document.getElementById('edit_numero_economico');
        const inpPlacas = document.getElementById('edit_placas');
        const inpMarca = document.getElementById('edit_marca');
        const inpModelo = document.getElementById('edit_modelo');
        const inpAnio = document.getElementById('edit_modelo_anio');
        const inpKm = document.getElementById('edit_km_actual');
        const inpPatrimonial = document.getElementById('edit_numero_patrimonial');
        const inpSerie = document.getElementById('edit_numero_serie');
        const selResguard = document.getElementById('edit_id_resguardante');
        const selEstado = document.getElementById('edit_estado_operativo');

        document.querySelectorAll('.btn-trigger-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                inpId.value = btn.dataset.id || '';
                inpEconomico.value = btn.dataset.economico || '';
                inpPlacas.value = btn.dataset.placas || '';
                inpMarca.value = btn.dataset.marca || '';
                inpModelo.value = btn.dataset.modelo || '';
                inpAnio.value = btn.dataset.anio || '';
                inpKm.value = btn.dataset.km || '0';
                inpPatrimonial.value = btn.dataset.patrimonial || '';
                inpSerie.value = btn.dataset.serie || '';
                selResguard.value = btn.dataset.resguardante || '0';
                selEstado.value = btn.dataset.estado || 'Disponible';

                openModal(modalEditar);
                inpEconomico.focus();
            });
        });
    }

    // ─── Módulo de Vehículos: Eliminar ─────────────────────────
    const modalEliminar = document.getElementById('modalEliminarVehiculo');
    if (modalEliminar) {
        const inpDeleteId = document.getElementById('delete_id_vehiculo');
        const txtDeleteInfo = document.getElementById('delete_vehiculo_info');

        document.querySelectorAll('.btn-trigger-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                inpDeleteId.value = btn.dataset.id || '';
                if (txtDeleteInfo) {
                    txtDeleteInfo.textContent = btn.dataset.info || 'Unidad seleccionada';
                }
                openModal(modalEliminar);
            });
        });
    }

    // ─── Módulo de Áreas: Editar ──────────────────────────────
    const modalEditarArea = document.getElementById('modalEditarArea');
    if (modalEditarArea) {
        const inpIdArea = document.getElementById('edit_id_area');
        const inpNombreArea = document.getElementById('edit_nombre_area');

        document.querySelectorAll('.btn-trigger-edit-area').forEach(btn => {
            btn.addEventListener('click', () => {
                inpIdArea.value = btn.dataset.id || '';
                inpNombreArea.value = btn.dataset.nombre || '';
                openModal(modalEditarArea);
                inpNombreArea.focus();
            });
        });
    }

    // ─── Módulo de Áreas: Eliminar ────────────────────────────
    const modalEliminarArea = document.getElementById('modalEliminarArea');
    if (modalEliminarArea) {
        const inpDeleteIdArea = document.getElementById('delete_id_area');
        const txtDeleteNombreArea = document.getElementById('delete_nombre_area');

        document.querySelectorAll('.btn-trigger-delete-area').forEach(btn => {
            btn.addEventListener('click', () => {
                inpDeleteIdArea.value = btn.dataset.id || '';
                if (txtDeleteNombreArea) {
                    txtDeleteNombreArea.textContent = btn.dataset.nombre || 'Área seleccionada';
                }
                openModal(modalEliminarArea);
            });
        });
    }

    // ─── Módulo de Conductores: Editar Licencia ────────────────
    const modalEditarConductor = document.getElementById('modalEditarConductor');
    if (modalEditarConductor) {
        const inpIdUser     = document.getElementById('edit_id_usuario');
        const txtNombre     = document.getElementById('edit_nombre_display');
        const inpLicencia   = document.getElementById('edit_numero_licencia');
        const inpVigencia   = document.getElementById('edit_vigencia_licencia');
        const inpFotoActual = document.getElementById('edit_foto_licencia_actual');

        document.querySelectorAll('.btn-trigger-edit-conductor').forEach(btn => {
            btn.addEventListener('click', () => {
                if (inpIdUser)     inpIdUser.value     = btn.dataset.id || '';
                if (txtNombre)     txtNombre.textContent = btn.dataset.nombre || 'Servidor Público';
                if (inpLicencia)   inpLicencia.value   = btn.dataset.licencia || '';
                if (inpVigencia)   inpVigencia.value   = btn.dataset.vigencia || '';
                if (inpFotoActual) inpFotoActual.value = btn.dataset.foto || '';
                openModal(modalEditarConductor);
                if (inpLicencia)   inpLicencia.focus();
            });
        });
    }

    // ─── Módulo de Conductores: Retirar del Padrón ─────────────
    const modalEliminarConductor = document.getElementById('modalEliminarConductor');
    if (modalEliminarConductor) {
        const inpDeleteId     = document.getElementById('delete_id_usuario');
        const txtDeleteNombre = document.getElementById('delete_nombre_conductor');

        document.querySelectorAll('.btn-trigger-delete-conductor').forEach(btn => {
            btn.addEventListener('click', () => {
                if (inpDeleteId)     inpDeleteId.value = btn.dataset.id || '';
                if (txtDeleteNombre) txtDeleteNombre.textContent = btn.dataset.nombre || 'Servidor Público seleccionado';
                openModal(modalEliminarConductor);
            });
        });
    }
    // ─── Módulo de Solicitudes: Evaluar y Asignar Unidad (evaluar.php) ──
    const modalEval = document.getElementById('modalEvaluarAsignar');
    if (modalEval) {
        const inpEvalId        = document.getElementById('eval_id_solicitud');
        const selVehiculo      = document.getElementById('eval_id_vehiculo');
        const txtComentarios   = document.getElementById('eval_comentarios');
        const txtResSolicitante= document.getElementById('eval_resumen_solicitante');
        const txtResArea       = document.getElementById('eval_resumen_area');
        const txtResDestino    = document.getElementById('eval_resumen_destino');
        const txtResTipo       = document.getElementById('eval_resumen_tipo');
        const txtResSalida     = document.getElementById('eval_resumen_salida');
        const txtResRetorno    = document.getElementById('eval_resumen_retorno');
        const txtResMotivo     = document.getElementById('eval_resumen_motivo');
        const txtResPasajeros  = document.getElementById('eval_resumen_pasajeros');
        const txtResLicNum     = document.getElementById('eval_resumen_licencia_num');
        const txtResLicEstado  = document.getElementById('eval_resumen_licencia_estado');
        const wrapItinerario   = document.getElementById('eval_wrap_itinerario');
        const listItinerario   = document.getElementById('eval_lista_itinerario');

        document.querySelectorAll('.btn-trigger-evaluar').forEach(btn => {
            btn.addEventListener('click', () => {
                const id           = btn.dataset.id || '';
                const folio        = btn.dataset.folio || ('#' + id);
                const solicitante  = btn.dataset.solicitante || '';
                const area         = btn.dataset.area || '';
                const destino      = btn.dataset.destino || '';
                const tipo         = btn.dataset.tipo || 'Local';
                const salida       = btn.dataset.salida || '';
                const retorno      = btn.dataset.retorno || '';
                const pasajeros    = btn.dataset.pasajeros || '1';
                const motivo       = btn.dataset.motivo || '';
                const licNum       = btn.dataset.licenciaNum || 'No registrada';
                const licTexto     = btn.dataset.licenciaTexto || '';
                const itinerarioRaw= btn.dataset.itinerario || '';

                if (inpEvalId) inpEvalId.value = id;
                if (txtResSolicitante) txtResSolicitante.textContent = `${solicitante} (${folio})`;
                if (txtResArea) txtResArea.textContent = area;
                if (txtResDestino) txtResDestino.textContent = destino;
                if (txtResTipo) txtResTipo.textContent = `Tipo: Comisión ${tipo}`;
                if (txtResSalida) txtResSalida.textContent = `Salida: ${salida}`;
                if (txtResRetorno) txtResRetorno.textContent = `Retorno: ${retorno}`;
                if (txtResMotivo) txtResMotivo.textContent = motivo;
                if (txtResPasajeros) txtResPasajeros.textContent = `Cupo: ${pasajeros} pasajero(s)`;
                if (txtResLicNum) txtResLicNum.textContent = `Licencia No. ${licNum}`;
                if (txtResLicEstado) txtResLicEstado.textContent = licTexto;

                // Desglose de Paradas Intermedias
                if (wrapItinerario && listItinerario) {
                    listItinerario.innerHTML = '';
                    let paradas = [];
                    if (itinerarioRaw) {
                        try {
                            paradas = JSON.parse(itinerarioRaw);
                        } catch (err) {
                            paradas = [];
                        }
                    }

                    if (Array.isArray(paradas) && paradas.length > 0) {
                        paradas.forEach((p, idx) => {
                            const li = document.createElement('li');
                            li.className = 'itinerario-modal-row';

                            const num = document.createElement('span');
                            num.className = 'itinerario-modal-num';
                            num.textContent = (idx + 1);

                            const info = document.createElement('div');
                            info.className = 'itinerario-modal-info';

                            const lugar = document.createElement('div');
                            lugar.className = 'itinerario-modal-lugar';
                            lugar.textContent = p.ubicacion || 'Escala intermedia';
                            info.appendChild(lugar);

                            if (p.motivo) {
                                const mot = document.createElement('div');
                                mot.className = 'itinerario-modal-motivo';
                                mot.textContent = 'Motivo: ' + p.motivo;
                                info.appendChild(mot);
                            }

                            li.appendChild(num);
                            li.appendChild(info);
                            listItinerario.appendChild(li);
                        });
                        wrapItinerario.classList.remove('form-group-hidden');
                    } else {
                        wrapItinerario.classList.add('form-group-hidden');
                    }
                }

                if (selVehiculo) selVehiculo.value = '';
                if (txtComentarios) txtComentarios.value = '';

                openModal(modalEval);
            });
        });
    }

    // ─── Módulo de Solicitudes: Rechazar Comisión (evaluar.php) ────────
    const modalRechazarCom = document.getElementById('modalRechazarComision');
    if (modalRechazarCom) {
        const inpRechazarId    = document.getElementById('rechazar_id_solicitud');
        const txtRechazarFolio = document.getElementById('rechazar_folio_texto');
        const txtRechazarSol   = document.getElementById('rechazar_solicitante_texto');
        const txtRechazarMot   = document.getElementById('rechazar_motivo');

        document.querySelectorAll('.btn-trigger-rechazar').forEach(btn => {
            btn.addEventListener('click', () => {
                if (inpRechazarId) inpRechazarId.value = btn.dataset.id || '';
                if (txtRechazarFolio) txtRechazarFolio.textContent = btn.dataset.folio || ('#' + btn.dataset.id);
                if (txtRechazarSol) txtRechazarSol.textContent = btn.dataset.solicitante || '';
                if (txtRechazarMot) txtRechazarMot.value = '';

                openModal(modalRechazarCom);
            });
        });
    }

    // ─── Módulo de Solicitudes: Detalle Histórico (Solo Lectura - evaluar.php) ────────
    const modalDetalle = document.getElementById('modalDetalleSolicitud');
    if (modalDetalle) {
        const detFolio         = document.getElementById('det_folio_titulo');
        const detSolicitante   = document.getElementById('det_solicitante');
        const detArea          = document.getElementById('det_area');
        const detDestino       = document.getElementById('det_destino');
        const detTipo          = document.getElementById('det_tipo');
        const detFechaSalida   = document.getElementById('det_fecha_salida');
        const detFechaRetorno  = document.getElementById('det_fecha_retorno');
        const detMotivo        = document.getElementById('det_motivo');
        const detPasajeros     = document.getElementById('det_pasajeros');
        const detWrapItin      = document.getElementById('det_wrap_itinerario');
        const detListItin      = document.getElementById('det_lista_itinerario');
        const detVehiculoInfo  = document.getElementById('det_vehiculo_info');
        const detVehiculoModel = document.getElementById('det_vehiculo_modelo');
        const detKmInicial     = document.getElementById('det_km_inicial');
        const detKmFinal       = document.getElementById('det_km_final');
        const detKmRecorridos  = document.getElementById('det_km_recorridos');
        const detEstadoBadge   = document.getElementById('det_estado_badge');
        const detJefe          = document.getElementById('det_jefe');
        const detFechaEval     = document.getElementById('det_fecha_eval');
        const detComentarios   = document.getElementById('det_comentarios');

        // Mapa de clases de badge según estatus
        const badgeClassMap = {
            'Concluida':  'badge--success',
            'Rechazada':  'badge--danger',
            'Cancelada':  'badge--secondary',
            'Autorizada': 'badge--info',
            'En curso':   'badge--info'
        };

        document.querySelectorAll('.btn-trigger-detalle-historico').forEach(btn => {
            btn.addEventListener('click', () => {
                const d = btn.dataset;

                // Sección 1: Datos de la Comisión
                if (detFolio) detFolio.textContent = '#' + (d.id || '');
                if (detSolicitante) detSolicitante.textContent = d.solicitante || '--';
                if (detArea) detArea.textContent = d.area || '--';
                if (detDestino) detDestino.textContent = d.destino || '--';
                if (detTipo) detTipo.textContent = 'Tipo: Comisión ' + (d.tipo || 'Local');
                if (detFechaSalida) detFechaSalida.textContent = 'Salida: ' + (d.fechaSalida || '—');
                if (detFechaRetorno) detFechaRetorno.textContent = 'Retorno: ' + (d.fechaRetorno || '—');
                if (detMotivo) detMotivo.textContent = d.motivo || '--';
                if (detPasajeros) detPasajeros.textContent = 'Cupo: ' + (d.pasajeros || '1') + ' pasajero(s)';

                // Sección 2: Itinerario de Paradas Intermedias
                if (detWrapItin && detListItin) {
                    detListItin.innerHTML = '';
                    let paradas = [];
                    const itinRaw = d.itinerario || '';
                    if (itinRaw) {
                        try {
                            paradas = JSON.parse(itinRaw);
                        } catch (err) {
                            paradas = [];
                        }
                    }

                    if (Array.isArray(paradas) && paradas.length > 0) {
                        paradas.forEach((p, idx) => {
                            const li = document.createElement('li');
                            li.className = 'itinerario-modal-row';

                            const num = document.createElement('span');
                            num.className = 'itinerario-modal-num';
                            num.textContent = (idx + 1);

                            const info = document.createElement('div');
                            info.className = 'itinerario-modal-info';

                            const lugar = document.createElement('div');
                            lugar.className = 'itinerario-modal-lugar';
                            lugar.textContent = p.ubicacion || 'Escala intermedia';
                            info.appendChild(lugar);

                            if (p.motivo) {
                                const mot = document.createElement('div');
                                mot.className = 'itinerario-modal-motivo';
                                mot.textContent = 'Motivo: ' + p.motivo;
                                info.appendChild(mot);
                            }

                            li.appendChild(num);
                            li.appendChild(info);
                            detListItin.appendChild(li);
                        });
                        detWrapItin.classList.remove('form-group-hidden');
                    } else {
                        detWrapItin.classList.add('form-group-hidden');
                    }
                }

                // Sección 3: Vehículo y Odómetro
                const placas = d.vehiculoPlacas || '';
                const eco    = d.vehiculoEco || '';
                const modelo = d.vehiculoModelo || '';

                if (detVehiculoInfo) {
                    if (placas) {
                        detVehiculoInfo.textContent = 'Placas: ' + placas + (eco ? ' — Eco #' + eco : '');
                    } else {
                        detVehiculoInfo.textContent = 'Sin unidad asignada';
                    }
                }
                if (detVehiculoModel) detVehiculoModel.textContent = modelo || '—';
                if (detKmInicial) detKmInicial.textContent = 'Km Inicial: ' + (d.kmInicial || '—');
                if (detKmFinal) detKmFinal.textContent = 'Km Final: ' + (d.kmFinal || '—');
                if (detKmRecorridos) detKmRecorridos.textContent = 'Distancia recorrida: ' + (d.kmRecorridos || '—');

                // Sección 4: Decisión y Dictamen Final
                const estado = d.estado || 'Concluida';
                if (detEstadoBadge) {
                    detEstadoBadge.textContent = estado;
                    detEstadoBadge.className = 'badge ' + (badgeClassMap[estado] || 'badge--info');
                }
                if (detJefe) detJefe.textContent = d.jefe || 'Administración';
                if (detFechaEval) detFechaEval.textContent = d.fechaEval || '—';
                if (detComentarios) detComentarios.textContent = d.comentarios || 'Sin observaciones registradas';

                openModal(modalDetalle);
            });
        });
    }

    // ─── Módulo de Solicitudes: Autorizar/Rechazar en solicitudes/index.php ─
    const modalAccionSol = document.getElementById('modalAccionSolicitud');
    if (modalAccionSol) {
        const formAccion    = document.getElementById('formAccionSolicitud');
        const inpId         = document.getElementById('accion_id_solicitud');
        const txtTipo       = document.getElementById('accion_tipo_texto');
        const txtFolio      = document.getElementById('accion_folio_texto');
        const txtSolicitante= document.getElementById('accion_solicitante_texto');
        const tituloModal   = document.getElementById('accion_modal_titulo');
        const btnConfirmar  = document.getElementById('btnConfirmarAccion');
        const wrapVehiculo  = document.getElementById('wrap_vehiculo_asignado');
        const selVehiculoAcc= document.getElementById('accion_id_vehiculo');

        document.querySelectorAll('.btn-trigger-accion-sol').forEach(btn => {
            btn.addEventListener('click', () => {
                const id          = btn.getAttribute('data-id') || '';
                const solicitante = btn.getAttribute('data-solicitante') || '';
                const tipo        = btn.getAttribute('data-tipo') || 'autorizar';

                if (inpId) inpId.value = id;
                if (txtFolio) txtFolio.textContent = '#' + id;
                if (txtSolicitante) txtSolicitante.textContent = solicitante;

                if (btnConfirmar) {
                    btnConfirmar.classList.remove('btn-modal-submit--success', 'btn-modal-submit--danger');
                }

                if (tipo === 'autorizar') {
                    if (formAccion) formAccion.action = 'index.php?action=solicitudes_aprobar';
                    if (tituloModal) tituloModal.textContent = 'Autorizar Solicitud con Asignación Vehicular';
                    if (txtTipo) txtTipo.textContent = 'AUTORIZAR Y ASIGNAR VEHÍCULO A';
                    if (btnConfirmar) {
                        btnConfirmar.textContent = 'Autorizar Solicitud';
                        btnConfirmar.classList.add('btn-modal-submit--success');
                    }
                    if (wrapVehiculo) {
                        wrapVehiculo.classList.remove('form-group-hidden');
                    }
                    if (selVehiculoAcc) {
                        selVehiculoAcc.required = true;
                        selVehiculoAcc.value = '';
                    }
                } else {
                    if (formAccion) formAccion.action = 'index.php?action=solicitudes_rechazar';
                    if (tituloModal) tituloModal.textContent = 'Rechazar Solicitud';
                    if (txtTipo) txtTipo.textContent = 'RECHAZAR';
                    if (btnConfirmar) {
                        btnConfirmar.textContent = 'Rechazar Solicitud';
                        btnConfirmar.classList.add('btn-modal-submit--danger');
                    }
                    if (wrapVehiculo) {
                        wrapVehiculo.classList.add('form-group-hidden');
                    }
                    if (selVehiculoAcc) {
                        selVehiculoAcc.required = false;
                        selVehiculoAcc.value = '';
                    }
                }

                openModal(modalAccionSol);
            });
        });
    }

    // ─── Módulo de Caseta: Despachar Salida ───────────────────────────────
    const modalDespacheSalida = document.getElementById('modalDespacheSalida');
    if (modalDespacheSalida) {
        const salidaIdSolicitud  = document.getElementById('salida_id_solicitud');
        const salidaIdVehiculo   = document.getElementById('salida_id_vehiculo');
        const salidaKmInicial    = document.getElementById('salida_km_inicial');
        const salidaKmHint       = document.getElementById('salida_km_actual_hint');
        const salidaResumen      = document.getElementById('salida_resumen');
        const salidaObservaciones= document.getElementById('salida_observaciones');
        const salidaWrapItin     = document.getElementById('salida_wrap_itinerario');
        const salidaListaItin    = document.getElementById('salida_lista_itinerario');

        document.querySelectorAll('.btn-trigger-salida').forEach(btn => {
            btn.addEventListener('click', () => {
                const idSolicitud   = btn.dataset.idSolicitud || '';
                const idVehiculo    = btn.dataset.idVehiculo  || '';
                const conductor     = btn.dataset.conductor   || 'Sin asignar';
                const vehiculo      = btn.dataset.vehiculo    || 'Vehículo no especificado';
                const destino       = btn.dataset.destino     || 'No especificado';
                const kmActual      = parseInt(btn.dataset.kmActual || '0', 10);
                const itinerarioRaw = btn.dataset.itinerario  || '';

                if (salidaIdSolicitud)   salidaIdSolicitud.value  = idSolicitud;
                if (salidaIdVehiculo)    salidaIdVehiculo.value   = idVehiculo;
                if (salidaKmInicial) {
                    salidaKmInicial.value = kmActual > 0 ? kmActual : '';
                    salidaKmInicial.min   = kmActual;
                }
                if (salidaKmHint)  salidaKmHint.textContent = `Odómetro actual registrado: ${kmActual.toLocaleString()} km. El valor debe ser mayor o igual.`;
                if (salidaResumen) salidaResumen.textContent = `Solicitud #${idSolicitud} — Conductor: ${conductor} — Vehículo: ${vehiculo} — Destino: ${destino}`;
                if (salidaObservaciones) salidaObservaciones.value = '';

                // Desglose de Escalas Autorizadas para Caseta
                if (salidaWrapItin && salidaListaItin) {
                    salidaListaItin.innerHTML = '';
                    let paradas = [];
                    if (itinerarioRaw) {
                        try {
                            paradas = JSON.parse(itinerarioRaw);
                        } catch (err) {
                            paradas = [];
                        }
                    }

                    if (Array.isArray(paradas) && paradas.length > 0) {
                        paradas.forEach((p, idx) => {
                            const li = document.createElement('li');
                            li.className = 'itinerario-modal-row';

                            const num = document.createElement('span');
                            num.className = 'itinerario-modal-num';
                            num.textContent = (idx + 1);

                            const info = document.createElement('div');
                            info.className = 'itinerario-modal-info';

                            const lugar = document.createElement('div');
                            lugar.className = 'itinerario-modal-lugar';
                            lugar.textContent = p.ubicacion || 'Escala intermedia';
                            info.appendChild(lugar);

                            if (p.motivo) {
                                const mot = document.createElement('div');
                                mot.className = 'itinerario-modal-motivo';
                                mot.textContent = 'Motivo: ' + p.motivo;
                                info.appendChild(mot);
                            }

                            li.appendChild(num);
                            li.appendChild(info);
                            salidaListaItin.appendChild(li);
                        });
                        salidaWrapItin.classList.remove('form-group-hidden');
                    } else {
                        salidaWrapItin.classList.add('form-group-hidden');
                    }
                }

                openModal(modalDespacheSalida);
                if (salidaKmInicial) salidaKmInicial.focus();
            });
        });
    }

    // ─── Módulo de Caseta: Registrar Retorno (Entrada) ───────────────────
    const modalRegistroRetorno = document.getElementById('modalRegistroRetorno');
    if (modalRegistroRetorno) {
        const retornoIdMovimiento = document.getElementById('retorno_id_movimiento');
        const retornoKmFinal      = document.getElementById('retorno_km_final');
        const retornoKmHint       = document.getElementById('retorno_km_inicial_hint');
        const retornoResumen      = document.getElementById('retorno_resumen');
        const retornoObservaciones= document.getElementById('retorno_observaciones');

        document.querySelectorAll('.btn-trigger-retorno').forEach(btn => {
            btn.addEventListener('click', () => {
                const idMovimiento = btn.dataset.idMovimiento || '';
                const vehiculo     = btn.dataset.vehiculo     || 'Vehículo no especificado';
                const conductor    = btn.dataset.conductor    || 'Sin asignar';
                const kmInicial    = parseInt(btn.dataset.kmInicial || '0', 10);

                if (retornoIdMovimiento) retornoIdMovimiento.value = idMovimiento;
                if (retornoKmFinal) {
                    retornoKmFinal.value = '';
                    retornoKmFinal.min   = kmInicial;
                }
                if (retornoKmHint)       retornoKmHint.textContent = `Km de salida registrado: ${kmInicial.toLocaleString()} km. El km final debe ser mayor o igual.`;
                if (retornoResumen)      retornoResumen.textContent = `Movimiento #${idMovimiento} — Conductor: ${conductor} — Unidad: ${vehiculo}`;
                if (retornoObservaciones) retornoObservaciones.value = '';

                openModal(modalRegistroRetorno);
                if (retornoKmFinal) retornoKmFinal.focus();
            });
        });
    }

    const elTopbarDate = document.querySelector('.topbar-date');
    if (elTopbarDate) {
        const actualizarReloj = () => {
            const ahora = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const d = pad(ahora.getDate());
            const m = pad(ahora.getMonth() + 1);
            const y = ahora.getFullYear();
            const h = pad(ahora.getHours());
            const min = pad(ahora.getMinutes());
            const s = pad(ahora.getSeconds());
            elTopbarDate.textContent = `${d}/${m}/${y} ${h}:${min}:${s}`;
        };
        setInterval(actualizarReloj, 1000);
    }

    // ─── Pestañas Unificadas en Evaluar Solicitudes (evaluar.php) ───
    const evalTabs = document.querySelectorAll('.eval-tab-btn');
    if (evalTabs.length > 0) {
        evalTabs.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-tab-target');
                if (!targetId) return;

                evalTabs.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                document.querySelectorAll('.eval-tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });

                const targetPane = document.getElementById(targetId);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    }

    // ─── Tarea 7: Gestión Dinámica de Paradas Intermedias en Solicitud ─
    const btnAgregarParada = document.getElementById('btn-agregar-parada');
    const contenedorParadas = document.getElementById('contenedor-paradas');

    if (btnAgregarParada && contenedorParadas) {
        const MAX_PARADAS = 8;

        const actualizarIndicesParadas = () => {
            const items = contenedorParadas.querySelectorAll('.parada-item');
            items.forEach((item, idx) => {
                const badge = item.querySelector('.parada-badge-num');
                if (badge) badge.textContent = (idx + 1);

                const inputUbicacion = item.querySelector('.parada-input-lugar');
                if (inputUbicacion) inputUbicacion.name = `paradas[${idx}][ubicacion]`;

                const inputMotivo = item.querySelector('.parada-input-motivo');
                if (inputMotivo) inputMotivo.name = `paradas[${idx}][motivo]`;
            });

            btnAgregarParada.disabled = (items.length >= MAX_PARADAS);
        };

        btnAgregarParada.addEventListener('click', (e) => {
            e.preventDefault();
            const cantActual = contenedorParadas.querySelectorAll('.parada-item').length;
            if (cantActual >= MAX_PARADAS) return;

            const nuevoIndex = cantActual;
            const paradaDiv = document.createElement('div');
            paradaDiv.className = 'parada-item';

            paradaDiv.innerHTML = `
                <span class="parada-badge-num">${nuevoIndex + 1}</span>
                <div class="parada-inputs-grid">
                    <input type="text" 
                           name="paradas[${nuevoIndex}][ubicacion]" 
                           class="parada-input parada-input-lugar" 
                           placeholder="Punto de escala o dependencia (ej. Notaría / Banco / Secretaría)" 
                           required 
                           maxlength="150">
                    <input type="text" 
                           name="paradas[${nuevoIndex}][motivo]" 
                           class="parada-input parada-input-motivo" 
                           placeholder="Motivo o trámite en esta escala (Opcional)" 
                           maxlength="150">
                </div>
                <button type="button" class="btn-parada-eliminar" title="Quitar escala">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            `;

            const btnEliminar = paradaDiv.querySelector('.btn-parada-eliminar');
            btnEliminar.addEventListener('click', () => {
                paradaDiv.remove();
                actualizarIndicesParadas();
            });

            contenedorParadas.appendChild(paradaDiv);
            actualizarIndicesParadas();

            const inputNuevo = paradaDiv.querySelector('.parada-input-lugar');
            if (inputNuevo) inputNuevo.focus();
        });
    }

    // ══════════════════════════════════════════════════════════════
    // SISTEMA DE NOTIFICACIONES POPUP (TOASTS) EN TIEMPO REAL
    // Y PUNTOS ROJOS EN SIDEBAR CON APAGADO INTELIGENTE
    // ══════════════════════════════════════════════════════════════
    const iniciarMonitorNotificaciones = () => {
        // No ejecutar en páginas de autenticación/login
        if (document.querySelector('.login-card') || document.querySelector('form[action*="login"]')) {
            return;
        }

        const INTERVALO_MS = 30000; // Consulta cada 30 segundos
        const KEY_PENDIENTES = 'notif_prev_pendientes';
        const KEY_DESPACHOS  = 'notif_prev_despachos';
        const KEY_INICIALIZADO = 'notif_monitor_iniciado';

        // Elementos de insignia numérica en sidebar
        const dotNavEvaluar = document.getElementById('dotNavEvaluar');
        const dotNavCaseta  = document.getElementById('dotNavCaseta');

        // Sincronización estricta con el conteo devuelto por la base de datos sin mutaciones redundantes
        const actualizarBadgeSidebar = (elemento, conteo) => {
            if (!elemento) return;
            const textoNuevo = conteo > 0 ? (conteo > 99 ? '99+' : String(conteo)) : '';
            if (elemento.textContent !== textoNuevo) {
                elemento.textContent = textoNuevo;
            }
            if (conteo > 0) {
                if (elemento.classList.contains('hidden')) {
                    elemento.classList.remove('hidden');
                }
            } else {
                if (!elemento.classList.contains('hidden')) {
                    elemento.classList.add('hidden');
                }
            }
        };

        // Asegurar la existencia del contenedor de toasts
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container';
            toastContainer.setAttribute('aria-live', 'polite');
            document.body.appendChild(toastContainer);
        }

        const mostrarToast = ({ tipo, titulo, mensaje, textoBoton, urlDestino }) => {
            // Evitar destruir o duplicar una alerta existente del mismo tipo
            const toastExistente = toastContainer.querySelector(`.toast-notification--${tipo}`);
            if (toastExistente) {
                const bodyMsg = toastExistente.querySelector('.toast-body');
                if (bodyMsg && bodyMsg.textContent !== mensaje) {
                    bodyMsg.textContent = mensaje;
                }
                return;
            }

            const toast = document.createElement('div');
            toast.className = `toast-notification toast-notification--${tipo}`;
            toast.setAttribute('role', 'alert');

            // Iconos SVG según el tipo de novedad
            const iconoSvg = (tipo === 'caseta')
                ? `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`;

            toast.innerHTML = `
                <div class="toast-header">
                    <div class="toast-title-wrap">
                        <span class="toast-icon">${iconoSvg}</span>
                        <span>${titulo}</span>
                    </div>
                    <button type="button" class="toast-close" title="Cerrar notificación">&times;</button>
                </div>
                <p class="toast-body">${mensaje}</p>
                <div class="toast-footer">
                    <a href="${urlDestino}" class="toast-action-btn">
                        ${textoBoton}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            `;

            const cerrar = () => {
                toast.classList.add('toast-fade-out');
                setTimeout(() => toast.remove(), 320);
            };

            // 1. Cierre manual con el botón (×)
            const btnClose = toast.querySelector('.toast-close');
            if (btnClose) {
                btnClose.addEventListener('click', cerrar);
            }

            // 2. Clic en botón de acción principal ("Ver Solicitudes" / "Ver Caseta")
            // Cierra el Toast; la insignia en sidebar se mantiene sincronizada con la base de datos
            const btnAction = toast.querySelector('.toast-action-btn');
            if (btnAction) {
                btnAction.addEventListener('click', cerrar);
            }

            // NOTA: Se eliminó el auto-cierre por temporizador (setTimeout).
            // El Popup permanece fijo en la esquina inferior derecha hasta la interacción del usuario.

            toastContainer.appendChild(toast);
        };

        const consultarNovedades = async () => {
            try {
                const res = await fetch('index.php?action=notificaciones_novedades', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) return;
                const data = await res.json();
                if (data.status !== 'success') return;

                const nuevoPendientes = parseInt(data.pendientes, 10) || 0;
                const nuevoDespachos  = parseInt(data.despachos_pendientes, 10) || 0;

                // Sincronización exacta de insignias numéricas con la BD
                actualizarBadgeSidebar(dotNavEvaluar, nuevoPendientes);
                actualizarBadgeSidebar(dotNavCaseta, nuevoDespachos);

                const prevPendientesRaw = sessionStorage.getItem(KEY_PENDIENTES);
                const prevDespachosRaw  = sessionStorage.getItem(KEY_DESPACHOS);
                const inicializado      = sessionStorage.getItem(KEY_INICIALIZADO);

                if (!inicializado || prevPendientesRaw === null) {
                    // Primera ejecución en la sesión de navegación: registrar conteos base
                    sessionStorage.setItem(KEY_PENDIENTES, nuevoPendientes);
                    sessionStorage.setItem(KEY_DESPACHOS, nuevoDespachos);
                    sessionStorage.setItem(KEY_INICIALIZADO, '1');
                    return;
                }

                const prevPendientes = parseInt(prevPendientesRaw, 10);
                const prevDespachos  = parseInt(prevDespachosRaw, 10);

                // 1. Detección de nuevas solicitudes pendientes (Admin / Encargado / Jefe)
                if (nuevoPendientes > prevPendientes) {
                    const dif = nuevoPendientes - prevPendientes;
                    const mensaje = (dif === 1)
                        ? 'Se ha registrado 1 nueva solicitud pendiente de evaluación.'
                        : `Se han registrado ${dif} nuevas solicitudes pendientes de evaluación.`;

                    mostrarToast({
                        tipo: 'solicitud',
                        titulo: 'Nueva Solicitud de Vehículo',
                        mensaje: mensaje,
                        textoBoton: 'Ver Solicitudes',
                        urlDestino: 'index.php?action=solicitudes_evaluar'
                    });
                }
                sessionStorage.setItem(KEY_PENDIENTES, nuevoPendientes);

                // 2. Detección de nuevos despachos autorizados en espera (Caseta / Encargado)
                if (nuevoDespachos > prevDespachos) {
                    const dif = nuevoDespachos - prevDespachos;
                    const mensaje = (dif === 1)
                        ? 'Hay 1 comisión autorizada en espera de despacho en caseta.'
                        : `Hay ${dif} comisiones autorizadas en espera de despacho en caseta.`;

                    mostrarToast({
                        tipo: 'caseta',
                        titulo: 'Vehículo Listo para Salida',
                        mensaje: mensaje,
                        textoBoton: 'Ver Caseta',
                        urlDestino: 'index.php?action=movimientos'
                    });
                }
                sessionStorage.setItem(KEY_DESPACHOS, nuevoDespachos);

            } catch (err) {
                // Silencioso en caso de desconexión momentánea
                console.debug('Error al verificar novedades:', err);
            }
        };

        // Primera consulta diferida (2.5 segundos) para no saturar la carga inicial
        setTimeout(consultarNovedades, 2500);

        // Polling en intervalos regulares de 30 segundos
        setInterval(consultarNovedades, INTERVALO_MS);
    };

    // Iniciar monitor de alertas en segundo plano
    iniciarMonitorNotificaciones();
});
