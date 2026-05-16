locals {
  tags = [var.project_name, var.environment, "terraform"]
}

resource "proxmox_vm_qemu" "this" {
  for_each = var.vm_definitions

  name        = each.value.name
  target_node = var.target_node
  clone       = var.vm_clone_template
  vmid        = each.value.vmid
  onboot      = true
  agent       = 1
  os_type     = "cloud-init"
  tags        = join(";", local.tags)

  cores   = each.value.cores
  sockets = each.value.sockets
  memory  = each.value.memory

  scsihw = "virtio-scsi-pci"
  boot   = "order=scsi0"

  disk {
    slot    = "scsi0"
    size    = each.value.disk_size
    type    = "disk"
    storage = var.storage_pool
  }

  network {
    id     = 0
    model  = "virtio"
    bridge = var.bridge
  }

  ciuser     = try(each.value.ci_user, "ubuntu")
  cipassword = try(each.value.ci_password, null)
  sshkeys    = var.ssh_public_keys
  ipconfig0  = "ip=${each.value.ip_address},gw=${var.gateway}"
  nameserver = var.dns_servers
}

resource "proxmox_lxc" "this" {
  for_each = var.lxc_definitions

  hostname     = each.value.hostname
  target_node  = var.target_node
  vmid         = each.value.vmid
  ostemplate   = "${var.storage_pool}:vztmpl/${var.lxc_template_file}"
  password     = each.value.password
  unprivileged = try(each.value.unprivileged, true)
  onboot       = true
  start        = true
  tags         = join(";", local.tags)

  cores  = each.value.cores
  memory = each.value.memory
  swap   = each.value.swap

  rootfs {
    storage = var.storage_pool
    size    = each.value.disk_size
  }

  network {
    name   = "eth0"
    bridge = var.bridge
    ip     = each.value.ip_address
    gw     = var.gateway
  }

  ssh_public_keys = var.ssh_public_keys
  nameserver      = var.dns_servers
  searchdomain    = "local"
}
