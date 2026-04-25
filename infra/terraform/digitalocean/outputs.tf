output "droplet_ids" {
  description = "IDs dos droplets criados."
  value       = { for key, droplet in digitalocean_droplet.this : key => droplet.id }
}

output "droplet_ipv4_addresses" {
  description = "IPs publicos IPv4 dos droplets."
  value       = { for key, droplet in digitalocean_droplet.this : key => droplet.ipv4_address }
}

output "droplet_ipv4_private_addresses" {
  description = "IPs privados dos droplets na VPC."
  value       = { for key, droplet in digitalocean_droplet.this : key => droplet.ipv4_address_private }
}

output "volume_ids" {
  description = "IDs dos volumes persistentes."
  value       = { for key, volume in digitalocean_volume.this : key => volume.id }
}

output "firewall_id" {
  description = "ID do firewall principal."
  value       = digitalocean_firewall.this.id
}
