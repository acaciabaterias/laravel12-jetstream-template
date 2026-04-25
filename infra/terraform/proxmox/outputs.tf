output "vm_ids" {
  description = "IDs das VMs criadas."
  value       = { for key, vm in proxmox_vm_qemu.this : key => vm.vmid }
}

output "vm_names" {
  description = "Nomes das VMs criadas."
  value       = { for key, vm in proxmox_vm_qemu.this : key => vm.name }
}

output "lxc_ids" {
  description = "IDs dos containers LXC criados."
  value       = { for key, lxc in proxmox_lxc.this : key => lxc.vmid }
}

output "lxc_hostnames" {
  description = "Hostnames dos containers LXC."
  value       = { for key, lxc in proxmox_lxc.this : key => lxc.hostname }
}

output "target_node" {
  description = "No do Proxmox usado no provisionamento."
  value       = var.target_node
}
