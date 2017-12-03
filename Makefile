.PHONY: doc

doc:
	rm -rf doc && mkdir doc
	phpdoc -d src/ -t doc_xml/ --template="xml"
	phpdocmd doc_xml/structure.xml doc
	rm -rf doc_xml
