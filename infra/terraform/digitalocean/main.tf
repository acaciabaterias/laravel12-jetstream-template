locals {
  default_tags = distinct(concat(var.common_tags, [var.project_name, var.environment, "terraform"]))

  droplets_with_volume = {
    for key, droplet in var.droplets : key => droplet
    if try(droplet.volume_size_gib, 0) > 0
  }
}

resource "digitalocean_droplet" "this" {
  for_each = var.droplets

  name       = each.value.name
  region     = var.region
  size       = each.value.size
  image      = each.value.image
  vpc_uuid   = var.vpc_uuid
  backups    = try(each.value.backups, false)
  monitoring = try(each.value.monitoring, true)
  ipv6       = try(each.value.ipv6, false)
  ssh_keys   = var.ssh_key_ids
  user_data  = try(each.value.user_data, null)
  tags       = distinct(concat(local.default_tags, [replace(each.value.name, "_", "-")]))
}

resource "digitalocean_volume" "this" {
  for_each = local.droplets_with_volume

  name                    = "${each.value.name}-data"
  region                  = var.region
  size                    = each.value.volume_size_gib
  initial_filesystem_type = "ext4"
  description             = "Volume persistente para ${each.value.name}"
}

resource "digitalocean_volume_attachment" "this" {
  for_each = local.droplets_with_volume

  droplet_id = digitalocean_droplet.this[each.key].id
  volume_id  = digitalocean_volume.this[each.key].id
}

resource "digitalocean_firewall" "this" {
  name        = "${var.project_name}-${var.environment}-firewall"
  droplet_ids = [for droplet in digitalocean_droplet.this : droplet.id]

  inbound_rule {
    protocol         = "tcp"
    port_range       = "22"
    source_addresses = var.allowed_ssh_cidrs
  }

  inbound_rule {
    protocol         = "tcp"
    port_range       = "80"
    source_addresses = var.allowed_http_cidrs
  }

  inbound_rule {
    protocol         = "tcp"
    port_range       = "443"
    source_addresses = var.allowed_http_cidrs
  }

  inbound_rule {
    protocol         = "icmp"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }

  outbound_rule {
    protocol              = "tcp"
    port_range            = "1-65535"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }

  outbound_rule {
    protocol              = "udp"
    port_range            = "1-65535"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }

  outbound_rule {
    protocol              = "icmp"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }

  tags = local.default_tags
}
