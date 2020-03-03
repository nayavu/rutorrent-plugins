
const columnsToRemove = ['save_path'];

theWebUI.tables.trt.columns = theWebUI.tables.trt.columns.filter( col => !columnsToRemove.includes(col.id) );
