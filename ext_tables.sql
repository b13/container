#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_container_parent int(11) DEFAULT '0' NOT NULL,
	KEY container_parent (tx_container_parent)
);
